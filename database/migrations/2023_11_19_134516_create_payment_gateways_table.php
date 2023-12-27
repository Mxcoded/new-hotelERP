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
            $table->string('name'); // Name of the payment gateway (e.g., PayPal, Stripe)
            $table->string('api_key')->nullable(); // API key for the gateway, if applicable
            $table->text('description')->nullable(); // A brief description or additional details
            $table->boolean('is_active')->default(true); // To enable or disable the gateway
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
