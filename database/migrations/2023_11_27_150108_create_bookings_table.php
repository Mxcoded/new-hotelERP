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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Link to the property
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('room_id')->nullable(); // For room bookings
            $table->unsignedBigInteger('facility_id')->nullable(); // For facility bookings
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['confirmed', 'cancelled', 'completed']);
            // Other fields as required
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties');
            $table->foreign('guest_id')->references('id')->on('guests');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
