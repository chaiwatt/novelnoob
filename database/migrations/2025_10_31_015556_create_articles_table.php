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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // slug ควรจะไม่ซ้ำกัน
            $table->text('meta_description')->nullable();
            $table->text('json_ld_description')->nullable();
            $table->longText('body');
            $table->json('tags')->nullable(); // เก็บ tags เป็น JSON
            $table->dateTime('public_date')->nullable();
            $table->char('is_video_public')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
