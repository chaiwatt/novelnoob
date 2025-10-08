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
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
                        // Foreign key to the users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // --- Core Novel Data ---
            $table->string('title'); // The final, AI-generated title
            $table->string('status')->default('outline_generated');
            $table->longText('outline_data'); // Store the full generated outline JSON

            // --- User Input / Blueprint Data ---
            $table->string('title_prompt'); // 'แนวทางชื่อเรื่อง'
            $table->string('character_nationality'); // 'สัญชาติตัวละคร'
            $table->text('setting_prompt'); // 'พล็อตเรื่อง / ฉาก'
            $table->string('style'); // 'เลือกสไตล์การเขียน' e.g., 'style_detective'
            $table->integer('act_count'); // 'เลือกโครงสร้างเรื่อง' e.g., 3, 4, or 5
            
            // --- Advanced Options (can be empty) ---
            $table->text('style_guide')->nullable(); // 'Style Guide (แก้ไขได้)'
            $table->text('genre_rules')->nullable(); // 'Genre Rules (แก้ไขได้)'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novels');
    }
};
