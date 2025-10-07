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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

         // ✅ เพิ่มบรรทัดนี้เพื่อเรียกใช้ Seeder ของเรา
        $this->call([
            ForexPriceAlertSeeder::class,
            // คุณสามารถเพิ่ม Seeder อื่นๆ ต่อที่นี่ได้
        ]);
    }
}
