<?php

namespace App\Console\Commands;

use App\Models\TwelvedataApi;
use App\Models\ForexPriceAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ForexPriceAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:forex-price-alert-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks forex prices, calculates pips away, and updates database with reversal check.';
    
    // ตั้งค่าขนาดชุด Symbol สูงสุดที่ API อนุญาตต่อการเรียก 1 ครั้ง
    protected const API_CHUNK_SIZE = 5;

     /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeframe = '4h'; // ตั้งค่า Timeframe ที่ต้องการ
        $this->info('Starting Forex Price Alert Command...');

        // 1. ดึงข้อมูล Alert ทั้งหมดจากฐานข้อมูล
        $forexPriceAlerts = ForexPriceAlert::all();
        
        if ($forexPriceAlerts->isEmpty()) {
            $this->info('No price alerts found to process.');
            return Command::SUCCESS;
        }

        // 2. แปลง Symbol จาก DB (USDJPY) เป็น API Format (USD/JPY)
        $processedSymbols = $forexPriceAlerts->pluck('pair')
            ->map(function ($pair) {
                if (strlen($pair) === 6) {
                    return substr($pair, 0, 3) . '/' . substr($pair, 3, 3);
                }
                return $pair;
            })
            ->unique()
            ->filter();
            
        $allSymbols = $processedSymbols;
        $symbolChunks = $allSymbols->chunk(self::API_CHUNK_SIZE);
        $finalHistoryData = [];

        // 3. วนลูปผ่านแต่ละชุด Symbol (Chunking) เพื่อเรียก API
        foreach ($symbolChunks as $chunk) {
            $currentSymbolList = $chunk->implode(',');
            $isSingleSymbol = $chunk->count() === 1;

            // 3.1 สุ่ม API Key
            $twelvedataApi = TwelvedataApi::inRandomOrder()->first();
            if (!$twelvedataApi) {
                $this->error('No available API key found in the database.');
                continue; 
            }
            $apiKey = $twelvedataApi->api;
            
            // 3.2 เรียก API (outputsize=3 เพื่อให้ได้ 3 แท่ง: 0, 1, 2)
            $response = Http::get("https://api.twelvedata.com/time_series?symbol={$currentSymbolList}&interval={$timeframe}&outputsize=3&apikey={$apiKey}");
            $data = $response->json();
            
            // 3.3 จัดการ Error
            if (isset($data['status']) && $data['status'] === 'error') {
                $this->error("API Error: " . ($data['message'] ?? 'Unknown API error'));
                continue; 
            }

            // 3.4 จัดโครงสร้างข้อมูลที่รับมาให้เป็นรูปแบบเดียวกัน
            if ($isSingleSymbol) {
                $dataToProcess = [ $chunk->first() => $data ];
            } else {
                $dataToProcess = $data;
            }
            
            // 3.5 ประมวลผลข้อมูล
            foreach ($chunk as $symbol) {
                if (isset($dataToProcess[$symbol]) && isset($dataToProcess[$symbol]['values'])) {
                    $values = $dataToProcess[$symbol]['values'];

                    $symbolHistory = collect($values)
                        ->reverse() // ทำให้แท่งที่เก่าสุดเป็น index 0 และล่าสุดเป็น index 2
                        ->values()
                        ->map(function ($item, $index) use ($symbol) {
                            return [
                                'symbol' => $symbol, 
                                'index' => $index, 
                                'datetime' => $item['datetime'],
                                'open' => (float) $item['open'], 
                                'high' => (float) $item['high'], 
                                'low' => (float) $item['low'],  
                                'close' => (float) $item['close']
                            ];
                        })->toArray();
                    
                    $finalHistoryData = array_merge($finalHistoryData, $symbolHistory);
                }
            }
        } 
        
        // 4. กรองเอาเฉพาะแท่งที่ Index 1 (Bar 2: แท่งก่อนล่าสุด) สำหรับคำนวณ Pips Away
        $pipsBarData = collect($finalHistoryData)
                            ->filter(fn($item) => $item['index'] === 1) 
                            ->values(); 

        if ($pipsBarData->isEmpty()) {
            $this->error('Failed to retrieve enough bar data for pips calculation.');
            return Command::FAILURE;
        }

        // 5. เตรียมข้อมูลราคาสำหรับการค้นหาที่รวดเร็ว
        $priceLookup = $pipsBarData->keyBy('symbol');
        $fullHistoryLookup = collect($finalHistoryData)->groupBy('symbol');
        $alertsToUpdate = [];

        // 6. วนลูป Alert จาก DB เพื่อคำนวณ Pips Away และตรวจสอบ Reversal
        foreach ($forexPriceAlerts as $alert) {
            $dbSymbol = $alert->pair;
            $apiSymbol = substr($dbSymbol, 0, 3) . '/' . substr($dbSymbol, 3, 3);
            $priceBar = $priceLookup->get($apiSymbol);

            if (!$priceBar) {
                continue;
            }

            $closePrice = $priceBar['close'];
            $targetPrice = (float) $alert->target_price;
            $type = strtoupper($alert->type);
            
            // Pip Multiplier
            $pipMultiplier = (str_contains($dbSymbol, 'JPY')) ? 100 : 10000;
            $difference = 0;
            
            // คำนวณ Pips Away
            if ($type === 'BUY') {
                $difference = $targetPrice - $closePrice;
            } elseif ($type === 'SELL') {
                $difference = $closePrice - $targetPrice;
            } else {
                continue;
            }

            $pipsAway = round($difference * $pipMultiplier, 2);
            $barDatetime = $priceBar['datetime']; // 👈 ดึง datetime ของแท่งที่ใช้คำนวณ (Index 1)

            // 👈 ---- START OF NEW LOGIC (STATEFUL CHECK) ----

            // --- STATE 4 CHECK ---
            // ถ้าเคยส่งแจ้งเตือนไปแล้ว (เสร็จสิ้น) ให้ข้าม Alert นี้ไปเลย
            if ($alert->last_alert_sent_at !== null) {
                continue;
            }

            // --- STATE 1 & 2 CHECK ---
            // ดึงสถานะปัจจุบันจากฐานข้อมูล
            $currentIsPriceReached = (bool) $alert->is_alert;
        
            if ($currentIsPriceReached === false) {
                // State 1: ยังรอราคา
                if ($pipsAway > 0) {
                    // ราคาเพิ่งถึงเป้าในรอบนี้! อัปเดตสถานะ
                    $currentIsPriceReached = true;
                }
                // ถ้า $pipsAway <= 0, $currentIsPriceReached จะยังคงเป็น false
            }
            
            // --- STATE 3 CHECK (REVERSAL) ---
            $newReversalFlag = 0; // ตั้งค่าเริ่มต้นเป็น 0 สำหรับรอบนี้
            $pendingPrice = null; // 👈 [NEW] ตั้งค่า pending_price เริ่มต้นเป็น null

            // ตรวจสอบ Reversal *ก็ต่อเมื่อ* ราคาถึงเป้าแล้ว (State 2 หรือ 3)
            if ($currentIsPriceReached) { 
                $currentHistory = $fullHistoryLookup->get($apiSymbol);

                if ($currentHistory && $currentHistory->count() >= 2) {
                    $barIndex0 = $currentHistory->firstWhere('index', 0); // แท่งเก่าสุด
                    $barIndex1 = $currentHistory->firstWhere('index', 1); // แท่งก่อนหน้า
                    
                    if ($barIndex0 && $barIndex1) {
                        $open = $barIndex1['open'];
                        $close = $barIndex1['close'];
                        $pendingPrice = min($close, $open) + abs($close - $open) / 2;
                        if ($type === 'BUY') {
                            // Reversal BUY: close[Index 1] - high[Index 0] > 0
                            if (($barIndex1['close'] - $barIndex0['high']) > 0) {
                                $newReversalFlag = 1;  
                            }
                        } elseif ($type === 'SELL') {
                            // Reversal SELL: close[Index 1] - low[Index 0] < 0
                            // dd($barIndex1['close'] , $barIndex1['low']);
                            if (($barIndex1['close'] - $barIndex0['low']) < 0) {
                                $newReversalFlag = 1;
                            }
                        }
                    }
                }
            }
            
            // 6.6 จัดเก็บผลการคำนวณ (ด้วยค่าที่อิงตามสถานะ)
            $alertsToUpdate[] = [
                'id'            => $alert->id,
                'pips_away'     => $pipsAway,
                'is_alert'      => $currentIsPriceReached, // 👈 ใช้ตัวแปรใหม่
                'close_price'   => $closePrice,
                'reversal_flag' => $newReversalFlag,       // 👈 ใช้ตัวแปรใหม่
                'bar_datetime'  => $barDatetime,
                'pending_price' => $pendingPrice,      // 👈 [NEW] เพิ่ม pending_price เข้า array
            ];
            // 👈 ---- END OF NEW LOGIC ----
        }

        // 7. 💥💥 อัปเดตฐานข้อมูลและส่ง Notification 💥💥
        // กำหนด Telegram Token และ Chat ID
        $telegramToken = env('TELEGRAM_BOT_TOKEN'); 
        $chatId = env('TELEGRAM_CHAT_ID'); 
        
        // ตรวจสอบว่ามี Token และ Chat ID หรือไม่
        if (empty($telegramToken) || empty($chatId)) {
            $this->error('Telegram BOT_TOKEN or CHAT_ID is missing in .env file. Cannot send notifications.');
        }
        
        $telegramUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
        
        $updateCount = 0;
        foreach ($alertsToUpdate as $result) {
            $alertModel = ForexPriceAlert::find($result['id']);

            if ($alertModel) {
                
                // ตรวจสอบเงื่อนไข Notification: (Price Reached) AND (Reversal Confirmed)
                $shouldNotify = $result['is_alert'] && ($result['reversal_flag'] === 1);
                
                // ตรวจสอบการแจ้งเตือนซ้ำ: 
                // 1. $shouldNotify ต้องเป็นจริง
                // 2. $isNewAlertData: ต้องเป็นข้อมูลแท่งเทียนใหม่ (ที่ datetime ไม่ตรงกับที่เคยส่งไปแล้ว)
                $isNewAlertData = $alertModel->last_alert_sent_at !== $result['bar_datetime'];

                if ($shouldNotify && $isNewAlertData && $telegramToken && $chatId) {
                    
                    // 🚨 Logic การแจ้งเตือน: Telegram
                    $messageContent = "*🚨 Forex Reversal Alert 🚨*\n\n"
                                    . "💰 *Pair:* {$alertModel->pair} ({$alertModel->type})\n"
                                    . "🎯 *Target Price:* {$alertModel->target_price}\n"
                                    . "📏 *Pips Past Target:* {$result['pips_away']} pips\n"
                                    . "📈 *Pending Price:* {$result['pending_price']}\n" // 👈 [NEW] เพิ่มราคากลางใน Notification
                                    . "🔄 *Reversal Confirmed!* ({$timeframe} Bar: {$result['bar_datetime']})";

                    try {
                        Http::post($telegramUrl, [
                            'chat_id' => $chatId,
                            'text' => $messageContent,
                            'parse_mode' => 'Markdown',
                        ]);
                        $this->info("Telegram Notification SENT for {$alertModel->pair}.");
                    } catch (\Exception $e) {
                        $this->error("Failed to send Telegram for {$alertModel->pair}: " . $e->getMessage());
                    }

                    // อัปเดต Datetime ของแท่งเทียนที่ถูกแจ้งเตือนล่าสุด เพื่อป้องกันการแจ้งซ้ำ
                    $alertModel->last_alert_sent_at = $result['bar_datetime']; 
                }
                
                // อัปเดตฐานข้อมูล (อัปเดตทุกครั้งที่รัน แม้จะยังไม่แจ้งเตือน)
                $alertModel->pips_away = (int) $result['pips_away']; 
                $alertModel->close_price = $result['close_price']; 
                $alertModel->reversal = $result['reversal_flag']; 
                // อัปเดต is_alert หากมีการเปลี่ยนแปลง (จาก false เป็น true)
                $alertModel->is_alert = $result['is_alert']; 

                // 👈 [NEW] อัปเดต pending_price เฉพาะเมื่อมีการคำนวณค่าใหม่
                if ($result['pending_price'] !== null) {
                    $alertModel->pending_price = $result['pending_price'];
                }
                
                $alertModel->save();
                $updateCount++;
            }
        }

        // 8. สรุปผล
        $this->info('Processing complete.');
        $this->info("Database update complete. Total {$updateCount} alerts had pips_away and reversal status updated.");
        
        return Command::SUCCESS; 
    }
}

