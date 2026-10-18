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
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('plan_slug');
            $table->string('plan_name');
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly, lifetime
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('gateway'); // stripe, paypal, razorpay, manual
            $table->string('transaction_id')->unique()->nullable();
            $table->string('payment_status')->default('pending'); // pending, completed, failed, refunded
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};