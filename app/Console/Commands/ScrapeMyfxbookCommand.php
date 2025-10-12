<?php

namespace App\Console\Commands;

use DOMXPath;
use Exception;
use DOMDocument;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\MyFxbookSentiment;

class ScrapeMyfxbookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-myfxbook-command';

    protected $description = 'Scrapes community outlook data from myfxbook.com';

/**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('▶️ Starting to scrape data from myfxbook.com...');

        // --------------------------------------------------------------------
        // 1. SETUP: กำหนดค่าเริ่มต้นและเวลา
        // --------------------------------------------------------------------
        $now = Carbon::now('Asia/Bangkok');
        $record_time = Carbon::now('UTC')->addHours(11)->startOfHour()->format('Y-m-d H:i:s');
        $url = "https://widgets.myfxbook.com/widgets/outlook.html?type=1&symbols=1,2,3,4,5,6,7,8,9,10,11,12,13,14,17,20,24,25,26,27,28,29,46,47,48,49,51,103,107";
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";


        // --------------------------------------------------------------------
        // 2. PRE-CHECK: ตรวจสอบว่าตลาดเปิดหรือไม่
        // --------------------------------------------------------------------
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $startMarketOpen = $startOfWeek->copy()->setTime(5, 0); // จันทร์ 05:00 ICT
        $endMarketOpen = $startOfWeek->copy()->addDays(4)->setTime(4, 0); // เสาร์ 04:00 ICT (เช้าวันเสาร์)

        if (!$now->between($startMarketOpen, $endMarketOpen)) {
            $this->warn('Market is closed. No data will be saved.');
            return 0;
        }
        $this->info("✅ Market is open. Record time for this batch: {$record_time}");


        // --------------------------------------------------------------------
        // 3. SCRAPING: ดึงข้อมูล HTML จากเว็บไซต์
        // --------------------------------------------------------------------
        try {
            $ch = curl_init();
            // ## CRITICAL FIX ## - แก้ไขตัวแปร $url ที่เคยตกหล่น
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            $html = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            curl_close($ch);

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table[@id="outlookSymbolsTable"]//tr[contains(@id, "outlookTiptool")]');
            
            if ($rows->length === 0) {
                $this->warn('No data rows found on the page. The website structure might have changed.');
                return 0;
            }

            $resultsForTable = [];

            // --------------------------------------------------------------------
            // 4. PROCESSING & SAVING: ประมวลผลและบันทึกข้อมูลทีละแถว
            // --------------------------------------------------------------------
            foreach ($rows as $row) {
                $symbolNode = $xpath->query('.//td[1]/a', $row)->item(0);
                if (!$symbolNode) continue;

                $symbol = trim($symbolNode->nodeValue);

                // --- ดึงข้อมูลดิบ ---
                $rawPendingBuy = $xpath->query('.//span[@id="shortPriceCell'.$symbol.'"]', $row)->item(0)->nodeValue ?? '0';
                $rawPendingSell = $xpath->query('.//span[@id="longPriceCell'.$symbol.'"]', $row)->item(0)->nodeValue ?? '0';
                
                $short_pct = 0;
                $long_pct = 0;
                $short_volume = '0';
                $long_volume = '0';

                $tooltipNode = $xpath->query('.//input[contains(@id, "outlookTip")]', $row)->item(0);
                if ($tooltipNode) {
                    $tooltipHtml = $tooltipNode->getAttribute('value');
                    $tooltipDom = new DOMDocument();
                    @$tooltipDom->loadHTML('<table>' . $tooltipHtml . '</table>');
                    $tooltipXpath = new DOMXPath($tooltipDom);
                    $shortRow = $tooltipXpath->query("//td[text()='Short']/parent::tr")->item(0);
                    $longRow = $tooltipXpath->query("//td[text()='Long']/parent::tr")->item(0);

                    if ($shortRow) {
                        $short_pct = (int) str_replace('%', '', $tooltipXpath->query('.//td[2]', $shortRow)->item(0)->nodeValue ?? '0%');
                        $short_volume = str_replace(' Lots', '', $tooltipXpath->query('.//td[3]', $shortRow)->item(0)->nodeValue ?? '0');
                    }
                    if ($longRow) {
                        $long_pct = (int) str_replace('%', '', $tooltipXpath->query('.//td[2]', $longRow)->item(0)->nodeValue ?? '0%');
                        $long_volume = str_replace(' Lots', '', $tooltipXpath->query('.//td[3]', $longRow)->item(0)->nodeValue ?? '0');
                    }
                }

                $short_pct = 100 - $long_pct;

                // --- บันทึกลงฐานข้อมูล ---
                MyFxbookSentiment::updateOrCreate(
                    [
                        'symbol'      => $symbol,
                        'record_time' => $record_time,
                    ],
                    [
                        'pendingbuy'       => (float) trim($rawPendingBuy),
                        'piptopendingbuy'  => 0, // คุณต้องเพิ่ม Logic การคำนวณเอง
                        'pendingsell'      => (float) trim($rawPendingSell),
                        'piptopendingsell' => 0, // คุณต้องเพิ่ม Logic การคำนวณเอง
                        'percentsell'      => $short_pct,
                        'percentbuy'       => $long_pct,
                        'sell_volume'      => (float) trim($short_volume),
                        'buy_volume'       => (float) trim($long_volume),
                    ]
                );

                // --- เตรียมข้อมูลสำหรับแสดงผลใน Console ---
                $resultsForTable[] = [
                    'Symbol' => $symbol, 'percentsell' => $short_pct, 'percentbuy' => $long_pct,
                    'sell_volume' => $short_volume, 'buy_volume' => $long_volume,
                    'pending buy' => $rawPendingBuy, 'pending sell' => $rawPendingSell,
                ];
            }

            // --------------------------------------------------------------------
            // 5. FINISH: แสดงผลสรุปและจบการทำงาน
            // --------------------------------------------------------------------
            // $this->line(''); 
            // $headers = ['Symbol', 'percentsell', 'percentbuy', 'sell_volume', 'buy_volume', 'pending buy', 'pending sell'];
            // $this->table($headers, $resultsForTable);
            $this->info("💾 Scraping and database update completed successfully for " . count($resultsForTable) . " symbols.");
            return 0;

        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
