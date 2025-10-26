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
        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // เช่น "แพ็กเกจ 1"
            $table->integer('credits'); // เช่น 900
            $table->integer('price'); // เช่น 300 (เก็บเป็น "สตางค์" ก็ได้ถ้าต้องการทศนิยม)
            $table->boolean('is_highlighted')->default(false); // ไฮไลท์ (คุ้มค่า)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};
