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
        Schema::create('forex_price_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('pair')->nullable();
            $table->double('close_price',10,5)->nullable();
            $table->string('type')->nullable();
            $table->double('target_price',10,5)->nullable();
            $table->char('pips_away')->default(0);
            $table->char('reversal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forex_price_alerts');
    }
};

     