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
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeframe = '4h'; // ตั้งค่า Timeframe ที่ต้องการ

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

        // 3. วนลูปผ่านแต่ละชุด Symbol (Chunking)
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
            
            // 3.5 ประมวลผลข้อมูล (รวม high/low/close ทั้งหมด)
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
                                'open' => (float) $item['open'], // เพิ่ม Open
                                'high' => (float) $item['high'], // เพิ่ม High
                                'low' => (float) $item['low'],   // เพิ่ม Low
                                'close' => (float) $item['close']
                            ];
                        })->toArray();
                    
                    $finalHistoryData = array_merge($finalHistoryData, $symbolHistory);
                }
            }
        } 
        
        // 4. กรองเอาเฉพาะแท่งที่ Index 1 (Bar 2: แท่งก่อนล่าสุด) สำหรับคำนวณ Pips Away
        $pipsBarData = collect($finalHistoryData)
                        ->filter(fn($item) => $item['index'] === 1) // ใช้ index 1 (แท่งก่อนล่าสุด)
                        ->values(); 

        if ($pipsBarData->isEmpty()) {
            $this->error('Failed to retrieve enough bar data for pips calculation.');
            return Command::FAILURE;
        }

        // 5. เตรียมข้อมูลราคาสำหรับการค้นหาที่รวดเร็ว
        $priceLookup = $pipsBarData->keyBy('symbol');
        $fullHistoryLookup = collect($finalHistoryData)->groupBy('symbol'); // สำหรับ Reversal Check
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
            $reversalFlag = 0; // ค่าเริ่มต้น

            // 6.5 💥💥 Logic ตรวจสอบ Reversal 💥💥
            // 🚨 เงื่อนไข: pipsAway > 0 (ราคาถึง/เลย Target แล้ว)
            if ($pipsAway > 0) { 
                $currentHistory = $fullHistoryLookup->get($apiSymbol);

                // ต้องมีแท่งเทียนอย่างน้อย 2 แท่ง (index 0, 1) สำหรับการคำนวณนี้
                if ($currentHistory && $currentHistory->count() >= 2) {
                    
                    // แท่งที่ 3 (เก่าสุด) คือ index 0 (แท่งที่ 1 ที่จะนำมาเปรียบเทียบ)
                    $barIndex0 = $currentHistory->firstWhere('index', 0); 
                    
                    // แท่งที่ 2 (Previous Bar) คือ index 1 (แท่งที่ 2 ที่จะนำมาเปรียบเทียบ)
                    $barIndex1 = $currentHistory->firstWhere('index', 1); 
                    
                    // ใช้ barIndex0 (Index 0) และ barIndex1 (Index 1) ในการคิด Reversal
                    if ($barIndex0 && $barIndex1) {
                        if ($type === 'BUY') {
                            // Reversal BUY: close[Index 1] - high[Index 0] > 0
                            // (ราคาปิดแท่ง Index 1 ทะลุ High ของแท่ง Index 0)
                            if (($barIndex1['close'] - $barIndex0['high']) > 0) {
                                $reversalFlag = 1;
                            }
                        } elseif ($type === 'SELL') {
                            // Reversal SELL: close[Index 1] - low[Index 0] < 0
                            // (ราคาปิดแท่ง Index 1 ทะลุ Low ของแท่ง Index 0)
                            if (($barIndex1['close'] - $barIndex0['low']) < 0) {
                                $reversalFlag = 1;
                            }
                        }
                    }
                }
            }
            
            // 6.6 จัดเก็บผลการคำนวณ
            $alertsToUpdate[] = [
                'id'            => $alert->id,
                'pips_away'     => $pipsAway,
                // 🚨 ถ้า pipsAway > 0 คือ Alert
                'is_alert'      => ($pipsAway > 0), 
                'close_price'   => $closePrice,
                'reversal_flag' => $reversalFlag, // บันทึกสถานะ Reversal
            ];
        }

        // 7. 💥💥 อัปเดตฐานข้อมูลด้วยค่า pips_away, close_price และ reversal 💥💥
        $updateCount = 0;
        foreach ($alertsToUpdate as $result) {
            $alertModel = ForexPriceAlert::find($result['id']);

            if ($alertModel) {
                $alertModel->pips_away = $result['pips_away'];
                $alertModel->close_price = $result['close_price']; 
                
                // 🚨 NEW: อัปเดตฟิลด์ reversal
                $alertModel->reversal = $result['reversal_flag']; 
                
                // 🚨 หากต้องการเพิ่ม Logic การแจ้งเตือน ให้ทำที่นี่
                
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

