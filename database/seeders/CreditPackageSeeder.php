<?php

namespace Database\Seeders;


use App\Models\CreditPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CreditPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // <-- 2. ปิดการตรวจสอบ Foreign Key -->
        Schema::disableForeignKeyConstraints();

        // <-- 3. ล้างข้อมูลเก่าในตาราง (Truncate) -->
        CreditPackage::truncate();

        // <-- 4. เปิดการตรวจสอบ Foreign Key กลับคืน -->
        Schema::enableForeignKeyConstraints();

        // 5. ใส่ข้อมูลใหม่ (เหมือนเดิม)
        $packages = [
            [
                'name' => 'แพ็กเกจ 1',
                'credits' => 900,
                'price' => 300,
                'is_highlighted' => false,
            ],
            [
                'name' => 'แพ็กเกจ 2',
                'credits' => 1600,
                'price' => 500,
                'is_highlighted' => true, // Highlighted package
            ],
            [
                'name' => 'แพ็กเกจ 3',
                'credits' => 3500,
                'price' => 1000,
                'is_highlighted' => false,
            ],
        ];

        foreach ($packages as $package) {
            CreditPackage::create($package);
        }
    }
}
