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
            ['pair' => 'USDJPY', 'type' => 'BUY', 'target_price' => 148.500, 'pips_away' => 15, 'reversal' => 0],
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
