<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_reports', function (Blueprint $table) {
            $table->id();
            // 1. Foreign Key: ผู้รายงาน
            $table->foreignId('user_id')
                  ->constrained('users') // อ้างอิงตาราง 'users'
                  ->onDelete('cascade'); // หากผู้ใช้ถูกลบ การรายงานที่ทำโดยผู้ใช้นั้นจะถูกลบไปด้วย

            // 2. Foreign Key: โพสต์ที่ถูกรายงาน
            $table->foreignId('post_id')
                  ->constrained('posts') // อ้างอิงตาราง 'posts'
                  ->onDelete('cascade'); // หากโพสต์ถูกลบ รายงานทั้งหมดที่เกี่ยวข้องกับโพสต์นั้นจะถูกลบไปด้วย

            // 3. ป้องกันการรายงานซ้ำ: ผู้ใช้คนเดียวกันรายงานโพสต์เดิมซ้ำไม่ได้
            $table->unique(['user_id', 'post_id']); 

            // 4. ข้อมูลเพิ่มเติมที่จำเป็นสำหรับ Admin (สถานะ)
            $table->string('status')->default('pending'); // pending, resolved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
