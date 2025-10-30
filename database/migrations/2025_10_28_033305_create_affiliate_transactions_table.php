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
        Schema::create('affiliate_transactions', function (Blueprint $table) {
            $table->id();
            // ID ของผู้แนะนำ (เจ้าของลิงก์)
            $table->foreignId('referrer_user_id')->constrained('users')->onDelete('restrict');
            
            // ID ของแพ็กเกจเครดิตที่ถูกซื้อ
            $table->foreignId('credit_package_id')->constrained('credit_packages')->onDelete('restrict');
            $table->string('referrer_masked_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_transactions');
    }
};
