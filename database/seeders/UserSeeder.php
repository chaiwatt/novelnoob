<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
   // ข้อมูลนักเขียน 10 คน ที่คุณเลือก
    private array $mockAuthors = [
        ['name' => 'ม่านมุก', 'avatar' => '6C5DD3'],
        ['name' => 'นักเดินทาง', 'avatar' => '5DD39E'],
        ['name' => 'เพียงฝัน', 'avatar' => '8375e7'],
        ['name' => 'เงาจันทร์', 'avatar' => 'D35D5D'],
        ['name' => 'สายลม', 'avatar' => '3b82f6'],
        ['name' => 'สมชาย', 'avatar' => 'A9B4D9'],
        ['name' => 'คิตติกร', 'avatar' => '7AA57B'],
        ['name' => 'สุรภี', 'avatar' => '9E767F'],
        ['name' => 'กฤต', 'avatar' => '66444A'],
        // เพิ่มนักเขียนคนที่ 10
        ['name' => 'เมฆา', 'avatar' => '40916C'], 
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User 1 - Admin 
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'credits' => 100000,
            'password' => Hash::make('11111111'),
            'type' => 'admin',
            'status' => 1,
            'affiliate' => Str::uuid(),
            'pen_name' => 'ผู้ดูแลระบบ',
            'avatar_url' => 'https://placehold.co/100x100/1F2937/FFFFFF?text=A' 
        ]);

        
        // 4. สร้างนักเขียนเพิ่มเติม 10 คน (ม่านมุก ถึง เมฆา)
        $i = 0;
        foreach ($this->mockAuthors as $author) {
            $i++;
            $initial = mb_substr($author['name'], 0, 1);
            $color = $author['avatar'];
            
            User::create([
                'name' => "Author{$i}",
                'email' => "author{$i}@novelnoob.com",
                'credits' => 100,
                'password' => Hash::make('11111111'),
                'type' => 'writer',
                'status' => 1,
                'affiliate' => Str::uuid(),
                'pen_name' => $author['name'], // กำหนด Pen Name ตามที่คุณต้องการ
                'avatar_url' => "https://placehold.co/100x100/{$color}/FFFFFF?text={$initial}"
            ]);
        }
    }
}
