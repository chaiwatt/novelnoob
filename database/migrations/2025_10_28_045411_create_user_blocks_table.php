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
        Schema::create('user_blocks', function (Blueprint $table) {
             $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key for the user being blocked
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade');
            
            // Define composite primary key to ensure uniqueness
            $table->primary(['blocker_id', 'blocked_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};
