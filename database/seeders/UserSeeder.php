<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    // ⭐️ [แก้ไข] เพิ่ม 'email_name' (ภาษาอังกฤษ) เข้าไปใน Array โดยตรง
    private array $mockAuthors = [
        ['name' => 'ม่านมุก', 'avatar' => '6C5DD3', 'email_name' => 'manmook'],
        ['name' => 'นักเดินทาง', 'avatar' => '5DD39E', 'email_name' => 'nakdernthang'],
        ['name' => 'เพียงฝัน', 'avatar' => '8375e7', 'email_name' => 'piangfan'],
        ['name' => 'เงาจันทร์', 'avatar' => 'D35D5D', 'email_name' => 'ngaowchan'],
        ['name' => 'สายลม', 'avatar' => '3b82f6', 'email_name' => 'sailom'],
        ['name' => 'ลำนำ', 'avatar' => 'A9B4D9', 'email_name' => 'lamnum'],
        ['name' => 'คิตติกร', 'avatar' => '7AA57B', 'email_name' => 'kittikorn'],
        ['name' => 'สุรภี', 'avatar' => '9E767F', 'email_name' => 'surapee'],
        ['name' => 'กฤต', 'avatar' => '66444A', 'email_name' => 'krit'],
        ['name' => 'เมฆา', 'avatar' => '40916C', 'email_name' => 'mekha'], 
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
        foreach ($this->mockAuthors as $author) {
            
            // ⭐️ [แก้ไข] ลบ Logic 'Str::slug()' ที่จัญไรทิ้งไป
            $penName = $author['name']; // 'ม่านมุก'
            $emailSlug = $author['email_name']; // ⭐️ ดึง 'manmook' มาโดยตรง

            // ⭐️ [แก้ไข] สร้าง Email จาก $emailSlug
            $email = $emailSlug . '@novelnoob.com'; // 'manmook@novelnoob.com'
            
            // ⭐️ [แก้ไข] สร้าง Name จาก $emailSlug (ทำให้เป็นตัวพิมพ์ใหญ่สวยงาม)
            $name = ucwords($emailSlug); // 'Manmook'
            
            // ⭐️ [แก้ไข] ดึงอักษรตัวแรก (ภาษาอังกฤษ) จาก $emailSlug
            $initial = mb_substr(strtoupper($emailSlug), 0, 1); // 'M'
            
            $color = $author['avatar'];
            
            User::create([
                'name' => $name, // ⭐️ 'Manmook'
                'email' => $email, // ⭐️ 'manmook@novelnoob.com'
                'credits' => 100,
                'password' => Hash::make('11111111'),
                'type' => 'writer',
                'status' => 1,
                'affiliate' => Str::uuid(),
                'pen_name' => $penName, // ⭐️ 'ม่านมุก'
                'avatar_url' => "https://placehold.co/100x100/{$color}/FFFFFF?text={$initial}" // ⭐️ 'M'
            ]);
        }
    }
}

