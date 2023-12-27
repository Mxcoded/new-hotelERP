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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('room_id')->nullable(); // Optional: For room-specific feedback
            $table->unsignedBigInteger('facility_id')->nullable(); // Optional: For facility-specific feedback
            $table->string('category')->nullable(); // E.g., service, cleanliness, amenities
            $table->text('comments');
            $table->integer('rating')->nullable(); // Optional: Numeric rating, if applicable
            $table->timestamps();

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
        Schema::dropIfExists('feedback');
    }
};
