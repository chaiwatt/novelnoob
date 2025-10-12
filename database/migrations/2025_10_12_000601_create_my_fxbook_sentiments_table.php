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
        Schema::create('my_fxbook_sentiments', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->double('pendingbuy', 15, 5)->nullable();  // เปลี่ยนเป็น double
            $table->integer('piptopendingbuy')->nullable();
            $table->char('percentbuy',10)->nullable();
            $table->char('buy_volume', 10)->nullable(); 
            $table->double('pendingsell', 15, 5)->nullable();  // เปลี่ยนเป็น double
            $table->integer('piptopendingsell')->nullable();
            $table->char('percentsell',10)->nullable();
            $table->char('sell_volume', 10)->nullable(); 
            $table->datetime('record_time')->nullable(); 
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_fxbook_sentiments');
    }
};
