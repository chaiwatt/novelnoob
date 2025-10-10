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
    protected $description = 'Command description';

    // ตั้งค่าขนาดชุด Symbol ที่ต้องการ
    protected const API_CHUNK_SIZE = 5;

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

        // 3. วนลูปผ่านแต่ละชุด Symbol (Chunking) พร้อมสุ่ม API Key
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
            
            // 3.2 เรียก API
            $response = Http::get("https://api.twelvedata.com/time_series?symbol={$currentSymbolList}&interval={$timeframe}&outputsize=2&apikey={$apiKey}");
            $data = $response->json();
            
            // 3.3 จัดการ Error
            if (isset($data['status']) && $data['status'] === 'error') {
                $this->error("API Error: " . ($data['message'] ?? 'Unknown API error'));
                continue; 
            }

            // 3.4 จัดโครงสร้างข้อมูลที่รับมาให้เป็นรูปแบบเดียวกัน (รองรับ Single/Multiple)
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
                        ->reverse() // ทำให้แท่งที่ 2 จากล่าสุดเป็น index 0
                        ->values()
                        ->map(function ($item, $index) use ($symbol) {
                            return [
                                'symbol' => $symbol, 
                                'index' => $index, 
                                'datetime' => $item['datetime'],
                                'close' => (float) $item['close'] // ดึงเฉพาะ Close price ที่จำเป็น
                            ];
                        })->toArray();
                    
                    $finalHistoryData = array_merge($finalHistoryData, $symbolHistory);
                }
            }
        } 
        
        // 4. กรองเอาเฉพาะแท่งก่อนล่าสุด (index 0)
        $prevBarData = collect($finalHistoryData)
                        ->filter(fn($item) => $item['index'] === 0)
                        ->values(); 

        if ($prevBarData->isEmpty()) {
            $this->error('Failed to retrieve any valid previous bar data.');
            return Command::FAILURE;
        }

        // 5. เตรียมข้อมูลราคาปัจจุบันสำหรับการค้นหาที่รวดเร็ว (KeyBy Symbol API Format)
        $priceLookup = $prevBarData->keyBy('symbol');
        $alertsToUpdate = [];

        // 6. วนลูป Alert จาก DB เพื่อคำนวณ Pips Away
        foreach ($forexPriceAlerts as $alert) {
            $dbSymbol = $alert->pair;
            $apiSymbol = substr($dbSymbol, 0, 3) . '/' . substr($dbSymbol, 3, 3);
            $priceBar = $priceLookup->get($apiSymbol);

            if (!$priceBar) {
                $this->warn("Skipping alert for {$dbSymbol}: Price data not found.");
                continue;
            }

            $closePrice = $priceBar['close'];
            $targetPrice = (float) $alert->target_price;
            $type = strtoupper($alert->type);
            
            // กำหนด Pip Multiplier
            $pipMultiplier = (str_contains($dbSymbol, 'JPY')) ? 100 : 10000;
            $difference = 0;
            
            // คำนวณ Difference ตาม Type
            if ($type === 'BUY') {
                $difference = $targetPrice - $closePrice;
            } elseif ($type === 'SELL') {
                $difference = $closePrice - $targetPrice;
            } else {
                continue;
            }

            $pipsAway = round($difference * $pipMultiplier, 2);
            
            // จัดเก็บผลการคำนวณ
            $alertsToUpdate[] = [
                'id'            => $alert->id,
                'pips_away'     => $pipsAway,
                'is_alert'      => ($pipsAway <= 0),
            ];
        }

        // 7. 💥💥 อัปเดตฐานข้อมูลด้วยค่า pips_away 💥💥
        $updateCount = 0;
        foreach ($alertsToUpdate as $result) {
            $alertModel = ForexPriceAlert::find($result['id']);

            if ($alertModel) {
                $alertModel->pips_away = $result['pips_away'];
                // 🚨 หากต้องการเพิ่ม Logic การแจ้งเตือน ให้ทำที่นี่
                $alertModel->save();
                $updateCount++;
            }
        }

        // 8. สรุปผล
        $this->info('Processing complete.');
        $this->info("Database update complete. Total {$updateCount} alerts had pips_away updated.");
        
        return Command::SUCCESS; 
    }
}

