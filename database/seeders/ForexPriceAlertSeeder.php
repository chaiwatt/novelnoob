<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ForexPriceAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // ล้างข้อมูลเก่าก่อน (ถ้าต้องการ)
        DB::table('forex_price_alerts')->delete();

        // เตรียมข้อมูลจาก Demo Data
        $alerts = [
            ['pair' => 'USDJPY', 'type' => 'BUY', 'target_price' => 148.500, 'pips_away' => 15, 'reversal' => 1],
            ['pair' => 'EURUSD', 'type' => 'SELL', 'target_price' => 1.0850, 'pips_away' => -25, 'reversal' => 0],
            ['pair' => 'GBPUSD', 'type' => 'BUY', 'target_price' => 1.2735, 'pips_away' => -8, 'reversal' => 0],
            ['pair' => 'AUDCAD', 'type' => 'SELL', 'target_price' => 0.8910, 'pips_away' => 5, 'reversal' => 0],
            ['pair' => 'EURJPY', 'type' => 'SELL', 'target_price' => 162.200, 'pips_away' => -40, 'reversal' => 0],
            ['pair' => 'USDCAD', 'type' => 'BUY', 'target_price' => 1.3680, 'pips_away' => 22, 'reversal' => 1],
            ['pair' => 'NZDUSD', 'type' => 'BUY', 'target_price' => 0.6155, 'pips_away' => -12, 'reversal' => 0],
            ['pair' => 'CHFJPY', 'type' => 'SELL', 'target_price' => 170.500, 'pips_away' => 30, 'reversal' => 1],
            ['pair' => 'GBPAUD', 'type' => 'BUY', 'target_price' => 1.9125, 'pips_away' => -18, 'reversal' => 0],
            ['pair' => 'EURAUD', 'type' => 'SELL', 'target_price' => 1.6430, 'pips_away' => -5, 'reversal' => 0],
        ];

        // เพิ่ม timestamps (created_at, updated_at) ให้กับทุก record
        $now = Carbon::now();
        $alertsWithTimestamps = array_map(function ($alert) use ($now) {
            $alert['created_at'] = $now;
            $alert['updated_at'] = $now;
            return $alert;
        }, $alerts);

        // Insert ข้อมูลลงในตาราง
        DB::table('forex_price_alerts')->insert($alertsWithTimestamps);
    }
}
