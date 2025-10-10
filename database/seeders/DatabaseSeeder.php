<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

         // ✅ เพิ่มบรรทัดนี้เพื่อเรียกใช้ Seeder ของเรา
        $this->call([
            ForexPriceAlertSeeder::class,
            UserSeeder::class,
            TwelvedataApiSeeder::class

        ]);


    }
}
