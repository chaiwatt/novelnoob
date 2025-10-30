<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ดึงข้อมูล User ทั้งหมดที่ถูกสร้างใน UserSeeder (สำคัญ)
        $users = User::all();
        $userIDs = $users->pluck('id');
        $authors = $users->filter(fn($user) => $user->type === 'writer' || $user->type === 'admin');

        // 2. ข้อมูล Mock Contents จาก JavaScript
        $mockContents = [
            "เพิ่งใช้ฟีเจอร์สร้างโครงเรื่องอัจฉริยะไปค่ะ ประทับใจมาก แค่ใส่ไอเดียไปไม่กี่ประโยคก็ได้พล็อตที่น่าสนใจกลับมาเลย",
            "กำลังเขียนนิยายแนวแฟนตาซีอยู่ครับ มีเคล็ดลับในการสร้างโลกยังไงให้น่าสนใจกันบ้างครับ มาแชร์กันหน่อย",
            "Writer's block เป็นอะไรที่ทรมานมาก แต่พอได้ใช้ฟีเจอร์ช่วยเขียนต่อแล้วรู้สึกไปต่อได้ง่ายขึ้นเยอะเลยค่ะ",
            "มีใครลองใช้ AI ช่วยสร้างตัวละครบ้างไหมครับ ผลลัพธ์ที่ได้น่าทึ่งมาก ได้ตัวละครที่มีมิติน่าสนใจสุดๆ",
            "เพิ่งเขียนนิยายเรื่องแรกจบด้วย Novel Noob ดีใจมากครับ! เป็นเครื่องมือที่เหมาะสำหรับมือใหม่จริงๆ",
            "เคล็ดลับการเขียน: ลองอ่านงานของนักเขียนหลายๆ ท่าน จะช่วยเปิดมุมมองและพัฒนาสำนวนของเราได้ดีมากครับ",
            "ตอนนี้กำลังติดนิยายแนวสืบสวนสอบสวน มีใครแนะนำเรื่องไหนเป็นพิเศษไหมครับ?",
            "การสร้าง Ebook ขายเองครั้งแรกตื่นเต้นมากเลยค่ะ มีใครมีประสบการณ์อยากแชร์ไหมคะ"
        ];
        
        // 3. ข้อมูล Mock Comments (ใช้เพื่อสุ่มเนื้อหาคอมเมนต์)
        $mockCommentContents = [
            "ลองแล้วเหมือนกันค่ะ ช่วยตอนคิดพล็อตไม่ออกได้ดีมากเลย!",
            "น่าสนใจมากครับ เดี๋ยวต้องไปลองใช้ดูบ้างแล้ว",
            "ของผมเน้นสร้างแผนที่กับประวัติศาสตร์ของโลกก่อนเลยครับ จะทำให้เขียนง่ายขึ้นเยอะ",
            "จริงค่ะ พอมีไทม์ไลน์แล้วเขียนสนุกขึ้นเยอะ",
            "เทคนิคดีมากเลยค่ะ ต้องลองทำตามบ้างแล้ว"
        ];

        $emojis = ['👍', '❤️', '😮', '😂', '😢', '😠'];

        // 4. สร้างโพสต์ 30 โพสต์ และความสัมพันธ์
        for ($i = 0; $i < 30; $i++) {
            // สุ่มผู้เขียนจากกลุ่มนักเขียน
            $author = $authors->random();

            $post = Post::create([
                'user_id' => $author->id,
                'content' => $mockContents[$i % count($mockContents)] ,
                'created_at' => now()->subMinutes(rand(1, 1440)), // สุ่มเวลาเป็นนาทีที่ผ่านมา
                'updated_at' => now(),
            ]);

            // 5. สร้าง Comments
            $commentCount = rand(0, 3);
            for ($c = 0; $c < $commentCount; $c++) {
                // สุ่มผู้เขียนคอมเมนต์จาก User ทั้งหมด
                $commentAuthor = $users->random(); 
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commentAuthor->id,
                    'content' => $mockCommentContents[$c % count($mockCommentContents)],
                    'created_at' => $post->created_at->addMinutes(rand(1, 60)),
                ]);
            }

            // 6. สร้าง Reactions (Likes)
            $likeCount = rand(5, 50);
            $likers = $userIDs->shuffle()->take($likeCount); 

            // FIX: ใช้วิธี detach/attach สำหรับ Pivot ที่ไม่มี ID
            // เนื่องจาก Reaction มีฟิลด์ reaction_type จึงต้องจัดการด้วยตัวเอง
            $post->reactions()->detach(); 

            foreach ($likers as $likerId) {
                $reactionType = $emojis[array_rand($emojis)];

                // FIX: ใช้ attach() แทน syncWithoutDetaching() สำหรับ Composite Key Pivot
                // เราต้องมั่นใจว่าไม่มีการซ้ำกันเกิดขึ้นก่อน
                if (!$post->reactions()->wherePivot('user_id', $likerId)->exists()) {
                     $post->reactions()->attach($likerId, [
                        'reaction_type' => $reactionType
                    ]);
                }
            }
            
            // 7. สร้าง Useful Marks
            $usefulCount = rand(0, 10);
            $usefulUsers = $userIDs->shuffle()->take($usefulCount); 
            
            // FIX: ใช้วิธี detach/attach สำหรับ Pivot ที่ไม่มี ID
            $post->usefuls()->detach();
            $post->usefuls()->attach($usefulUsers); // FIX: ใช้ attach()
        }
    }
}