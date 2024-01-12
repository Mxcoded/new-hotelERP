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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('reservation_id')->nullable(); // Link to reservation
            $table->unsignedBigInteger('property_id')->nullable(); // Link to property
            $table->string('invoice_number')->unique(); // New field for the invoice number
            $table->decimal('total_amount', 8, 2);
            $table->date('issue_date');
            $table->enum('status', ['unpaid', 'paid', 'cancelled']);
            $table->enum('type', ['master_bill', 'deposit_slip']); // New field
            $table->text('description')->nullable(); // New field
            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('guests');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
