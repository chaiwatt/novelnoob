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
        Schema::create('novel_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->onDelete('cascade');
            $table->integer('chapter_number');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->text('ending_summary')->nullable(); // เพิ่มฟิลด์สำหรับเก็บเนื้อหาท้ายบท
            $table->unsignedInteger('word_count')->default(3000); // เพิ่มฟิลด์สำหรับเก็บจำนวนคำ
            $table->string('status')->default('pending'); // e.g., pending, writing, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novel_chapters');
    }
};
