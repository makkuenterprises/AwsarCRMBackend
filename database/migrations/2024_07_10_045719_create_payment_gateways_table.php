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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the payment gateway (e.g., Razorpay)
            $table->string('api_key'); // API Key for the payment gateway
            $table->string('api_secret'); // API Secret for the payment gateway
            $table->string('webhook_secret')->nullable(); // Webhook secret for validating webhook events
            $table->text('description')->nullable(); // Optional description of the payment gateway
            $table->boolean('is_active')->default(true); // Status of the payment gateway (active/inactive)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
