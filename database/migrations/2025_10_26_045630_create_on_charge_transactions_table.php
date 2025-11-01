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
        Schema::create('on_charge_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->nullable();
            $table->string('charge_id')->unique(); // charge_id ควรไม่ซ้ำ
            $table->string('status'); // pending, successful, failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('on_charge_transactions');
    }
};
