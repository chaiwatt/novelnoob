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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_package_id')->nullable()->constrained()->onDelete('set null'); // Allow package deletion without losing history
            $table->unsignedBigInteger('credits_added');
            $table->decimal('amount_paid', 10, 2); // Store price paid
            $table->string('status')->default('completed'); // Default to completed for simulation
            $table->json('transaction_details')->nullable(); // For extra info
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
