<?php

namespace App\Http\Controllers;

use DOMXPath;
use DOMElement;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\MyFxbookSentiment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class MyFxbookController extends Controller
{
    // public function getSentiment(Request $request)
    // {
    //     $symbol = $request->symbol;
        
    //     // $sentiments = MyFxbookSentiment::where('symbol', $symbol)->get();
    //     $sentiments = MyFxbookSentiment::where('symbol', $symbol)
    //                 ->take(300)  // หรือ ->limit(300)
    //                 ->get();
        
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $sentiments
    //     ], 200);
    // }


    public function getSentiment(Request $request)
    {
        $symbol = $request->symbol;
        $limit = 3000;

        $sentiments = MyFxbookSentiment::where('symbol', $symbol)
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        

        return response()->json([
            'status' => 'success',
            'data' => $sentiments
        ], 200);
    }

public function getBatchSentiment(Request $request)
{
    $symbols = $request->symbols;
    $limit = 250;

    // Query เดียวด้วย ROW_NUMBER เพื่อเลือก record ล่าสุดต่อ symbol
    $allSentiments = MyFxbookSentiment::select('*')
        ->from(DB::raw("
            (
                SELECT *,
                    ROW_NUMBER() OVER (PARTITION BY symbol ORDER BY created_at DESC) as rn
                FROM my_fxbook_sentiments
                WHERE symbol IN (".implode(',', array_fill(0, count($symbols), '?')).")
            ) as ranked
        "))
        ->setBindings($symbols) // Bind parameters for the WHERE IN clause
        ->where('rn', '<=', $limit)
        ->orderBy('symbol')
        ->orderBy('created_at', 'desc')
        ->get();

    // Group ผลลัพธ์ใน PHP
    $grouped = $allSentiments->groupBy('symbol')->map(function ($items) {
        return $items->values();
    });

    return response()->json([
        'status' => 'success',
        'data' => $grouped
    ], 200);
}

    public function getBreakoutCount(Request $request)
    {
        // กำหนด symbols แบบคงที่
        $symbols = [
            "AUDCAD", "AUDCHF", "AUDJPY", "AUDNZD", "AUDUSD", 
            "CADCHF", "CADJPY", "CHFJPY", "EURAUD", "EURCAD", 
            "EURCHF", "EURGBP", "EURJPY", "EURNZD", "EURUSD", 
            "GBPAUD", "GBPCAD", "GBPCHF", "GBPJPY", "GBPNZD", 
            "GBPUSD", "NZDCAD", "NZDCHF", "NZDJPY", "NZDUSD", 
            "USDCAD", "USDCHF", "USDJPY", "XAUUSD"
        ];

        // สร้าง collection ใหม่
        $result = collect();

        foreach ($symbols as $symbol) {
            // ดึง record ล่าสุดที่มี piptopendingbuy เป็นบวก
            $latestPiptopendingbuy = MyFxbookSentiment::where('symbol', $symbol)
                ->where('piptopendingbuy', '>', 0)
                ->orderBy('record_time', 'desc')
                ->first();

            // ดึง record ล่าสุดที่มี piptopendingsell เป็นบวก
            $latestPiptopendingsell = MyFxbookSentiment::where('symbol', $symbol)
                ->where('piptopendingsell', '>', 0)
                ->orderBy('record_time', 'desc')
                ->first();

            // สร้าง array สำหรับ symbol นี้
            $symbolData = ['symbol' => $symbol];

            // นับจำนวน piptopendingbuy ที่เป็นบวกติดต่อกันจนเจอค่าลบ
            if ($latestPiptopendingbuy) {
                // หา record แรกที่มีค่า <= 0 ก่อนหน้า latestPiptopendingbuy
                $firstNegativePiptopendingbuy = MyFxbookSentiment::where('symbol', $symbol)
                    ->where('piptopendingbuy', '<=', 0)
                    ->where('record_time', '<', $latestPiptopendingbuy->record_time)
                    ->orderBy('record_time', 'desc')
                    ->first();

                // นับจำนวน record ที่ piptopendingbuy > 0 ตั้งแต่หลังค่าลบจนถึงล่าสุด
                $breakoutPiptopendingbuyCount = MyFxbookSentiment::where('symbol', $symbol)
                    ->where('piptopendingbuy', '>', 0)
                    ->where('record_time', '<=', $latestPiptopendingbuy->record_time)
                    ->when($firstNegativePiptopendingbuy, function ($query) use ($firstNegativePiptopendingbuy) {
                        return $query->where('record_time', '>', $firstNegativePiptopendingbuy->record_time);
                    })
                    ->count();

                $symbolData['breakout_piptopendingbuy_count'] = $breakoutPiptopendingbuyCount;
            }

            // นับจำนวน piptopendingsell ที่เป็นบวกติดต่อกันจนเจอค่าลบ
            if ($latestPiptopendingsell) {
                // หา record แรกที่มีค่า <= 0 ก่อนหน้า latestPiptopendingsell
                $firstNegativePiptopendingsell = MyFxbookSentiment::where('symbol', $symbol)
                    ->where('piptopendingsell', '<=', 0)
                    ->where('record_time', '<', $latestPiptopendingsell->record_time)
                    ->orderBy('record_time', 'desc')
                    ->first();

                // นับจำนวน record ที่ piptopendingsell > 0 ตั้งแต่หลังค่าลบจนถึงล่าสุด
                $breakoutPiptopendingsellCount = MyFxbookSentiment::where('symbol', $symbol)
                    ->where('piptopendingsell', '>', 0)
                    ->where('record_time', '<=', $latestPiptopendingsell->record_time)
                    ->when($firstNegativePiptopendingsell, function ($query) use ($firstNegativePiptopendingsell) {
                        return $query->where('record_time', '>', $firstNegativePiptopendingsell->record_time);
                    })
                    ->count();

                $symbolData['breakout_piptopendingsell_count'] = $breakoutPiptopendingsellCount;
            }

            // เพิ่มเข้า collection ถ้ามีอย่างน้อยหนึ่งตัวที่นับได้
            if (count($symbolData) > 1) { // มากกว่า 1 เพราะมี 'symbol' อยู่แล้ว
                $result->push($symbolData);
            }
        }

        return response()->json($result);
    }


public function isMarketOpen()
{
    $now = Carbon::now('Asia/Bangkok');

    // กำหนดช่วงเวลาที่ตลาดเปิด
    // ตลาดเปิด: จันทร์ 05:00 - เสาร์ 06:00
    // ตลาดปิด: เสาร์ 06:01 - จันทร์ 04:59
    $startOfWeek = $now->copy()->startOfWeek(); // จันทร์ 00:00 ICT
    $startMarketOpen = $startOfWeek->copy()->setTime(5, 0); // จันทร์ 05:00 ICT
    $endMarketOpen = $startOfWeek->copy()->addDays(5)->setTime(6, 0); // เสาร์ 06:00 ICT


    // ตรวจสอบว่าอยู่ในช่วงตลาดเปิดหรือไม่
    if ($now->between($startMarketOpen, $endMarketOpen)) {
        // เงื่อนไขเมื่ออยู่ในช่วงตลาดเปิด (จันทร์ 05:00 - เสาร์ 06:00)
        // ใส่โค้ดที่ต้องการให้ทำงานเมื่อตลาดเปิด
        dd('open');
    } else {
        // เงื่อนไขเมื่ออยู่ในช่วงตลาดปิด (เสาร์ 06:01 - จันทร์ 04:59)
        dd('close');
    }
}

public function getSentiment2()
{
    // Define the symbols array
    $symbols = [
        "AUDCAD", "AUDCHF", "AUDJPY", "AUDNZD", "AUDUSD", 
        "CADCHF", "CADJPY", "CHFJPY", "EURAUD", "EURCAD", 
        "EURCHF", "EURGBP", "EURJPY", "EURNZD", "EURUSD", 
        "GBPAUD", "GBPCAD", "GBPCHF", "GBPJPY", "GBPNZD", 
        "GBPUSD", "NZDCAD", "NZDCHF", "NZDJPY", "NZDUSD", 
        "USDCAD", "USDCHF", "USDJPY", "XAUUSD"
    ];

    // Initialize Guzzle client
    $client = new Client();
    $url = "https://www.myfxbook.com/community/outlook";

    // Fetch the page
    $response = $client->get($url, [
        'headers' => [
            'Accept' => 'text/html',
            'Content-Type' => 'text/html; charset=UTF-8',
        ]
    ]);

    // Get the HTML content
    $html = $response->getBody()->getContents();

    // Parse HTML with DOMDocument
    $doc = new DOMDocument();
    @libxml_use_internal_errors(true); // Suppress warnings from malformed HTML
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);

    // Second part: Outlook symbols table with symbol filtering
    $table = $xpath->query("//table[@id='outlookSymbolsTable']")->item(0);
    $tbody = $xpath->query(".//tbody[@id='outlookSymbolsTableContent']", $table)->item(0);
    $trs = $xpath->query(".//tr[contains(@class, 'outlook-symbol-row')]", $tbody);
    $pricelist = [];

    foreach ($trs as $tr) {
        $tds = $xpath->query(".//td", $tr);
        
        $symbol = trim($xpath->query(".//a", $tds->item(0))->item(0)->textContent);

        // Check if symbol is in the predefined symbols array
        if (in_array($symbol, $symbols)) {
            $tdavgshort = $tds->item(3);
            $tdavglong = $tds->item(4);
            
            $sp_short = $xpath->query(".//span", $tdavgshort);
            $sp_long = $xpath->query(".//span", $tdavglong);

            // Extract pip values and convert to integer
            $piptopendingbuy = (int) str_replace(' pips', '', trim($sp_short->item(1)->textContent));
            $piptopendingsell = (int) str_replace(' pips', '', trim($sp_long->item(1)->textContent));

            // Extract percentage values from progress bars
            $progressTd = $tds->item(1); // TD ที่มี progress bars
            $progressBars = $xpath->query(".//div[contains(@class, 'progress-bar')]", $progressTd);
            
            $percentSell = 0;
            $percentBuy = 0;
            
            foreach ($progressBars as $bar) {
                if ($bar instanceof DOMElement) {
                    $style = $bar->getAttribute('style');
                    preg_match('/width:\s*(\d+)%/', $style, $matches);
                    $width = isset($matches[1]) ? (int)$matches[1] : 0;
                    
                    $class = $bar->getAttribute('class');
                    if (strpos($class, 'progress-bar-danger') !== false) {
                        $percentSell = $width; // progress-bar-danger = percentSell
                    } elseif (strpos($class, 'progress-bar-success') !== false) {
                        $percentBuy = $width; // progress-bar-success = percentBuy
                    }
                }
            }

            // Extract buy and sell volumes from the nested table
            $hiddenTd = $tds->item(7); // <td style="display: none;">
            $volumeRows = $xpath->query(".//table/tbody/tr", $hiddenTd);
            
            $sellVolume = 0;
            $buyVolume = 0;
            
            foreach ($volumeRows as $index => $row) {
                $cells = $xpath->query(".//td", $row);
                
                // สำหรับแถวแรก (Short) มี 5 คอลัมน์
                // สำหรับแถวที่สอง (Long) มี 4 คอลัมน์ เนื่องจาก rowspan
                if ($index === 0) {
                    // Short (sell) row
                    $volume = trim($cells->item(3)->textContent); // Volume คอลัมน์ที่ 4
                    $sellVolume = (float) str_replace(' lots', '', $volume);
                } elseif ($index === 1) {
                    // Long (buy) row
                    $volume = trim($cells->item(2)->textContent); // Volume คอลัมน์ที่ 3 (เลื่อนไป 1 คอลัมน์)
                    $buyVolume = (float) str_replace(' lots', '', $volume);
                }
            }

            $now = Carbon::now('Asia/Bangkok');
            
            $startOfWeek = $now->copy()->startOfWeek(); // จันทร์ 00:00 ICT
            $startMarketOpen = $startOfWeek->copy()->setTime(5, 0); // จันทร์ 05:00 ICT
            $endMarketOpen = $startOfWeek->copy()->addDays(5)->setTime(6, 0); // เสาร์ 06:00 ICT
            
            // Get the current time and convert to GMT+11 Australia, rounded down to the nearest hour
            $record_time = Carbon::now('UTC')->addHours(11)->startOfHour()->format('Y-m-d H:i:s');

            if ($now->between($startMarketOpen, $endMarketOpen)) {
                // เงื่อนไขเมื่ออยู่ในช่วงตลาดเปิด (จันทร์ 05:00 - เสาร์ 06:00)
                MyFxbookSentiment::updateOrCreate(
                    [
                        'symbol' => $symbol,
                        'record_time' => $record_time, // Unique constraint based on symbol and record_time
                    ],
                    [
                        'pendingbuy' => trim($sp_short->item(0)->textContent),
                        'piptopendingbuy' => $piptopendingbuy,
                        'pendingsell' => trim($sp_long->item(0)->textContent),
                        'piptopendingsell' => $piptopendingsell,
                        'percentsell' => $percentSell,
                        'percentbuy' => $percentBuy,
                        'sell_volume' => $sellVolume, // เพิ่ม sell volume
                        'buy_volume' => $buyVolume,   // เพิ่ม buy volume
                    ]
                );
            }

            $pricelist[] = [
                'symbol' => $symbol,
                'pendingbuy' => trim($sp_short->item(0)->textContent),
                'piptopendingbuy' => $piptopendingbuy,
                'pendingsell' => trim($sp_long->item(0)->textContent),
                'piptopendingsell' => $piptopendingsell,
                'percentsell' => $percentSell,
                'percentbuy' => $percentBuy,
                'sell_volume' => $sellVolume, // เพิ่ม sell volume ใน pricelist
                'buy_volume' => $buyVolume,   // เพิ่ม buy volume ใน pricelist
            ];
        }
    }

    dd($pricelist);

    // return response()->json([
    //     'price_list' => $pricelist
    // ]);
}

}
