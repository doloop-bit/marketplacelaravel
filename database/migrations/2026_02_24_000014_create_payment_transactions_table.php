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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('payment_method');
            $table->string('payment_type')->nullable(); // e.g., 'credit_card', 'bank_transfer'
            $table->string('status'); // 'pending', 'success', 'failed', 'expired'
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->text('payment_response')->nullable(); // Store full response from payment gateway
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
