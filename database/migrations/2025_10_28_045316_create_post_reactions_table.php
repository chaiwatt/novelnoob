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
        Schema::create('post_reactions', function (Blueprint $table) {

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            
            // reaction_type: เก็บประเภท reaction (emoji เช่น '👍', '❤️')
            $table->string('reaction_type', 10); 
            
            // กำหนด Primary Key เป็นคู่ (user_id, post_id) 
            // **ลบ $table->id() ออกแล้ว** เพื่อใช้ Composite Primary Key
            $table->primary(['user_id', 'post_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
