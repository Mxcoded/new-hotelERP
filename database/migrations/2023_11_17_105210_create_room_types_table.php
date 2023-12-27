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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Link to a property
            $table->string('name'); // Room type name, e.g., Standard, Deluxe, Suite
            $table->text('description')->nullable(); // Optional description of the room type
            $table->decimal('base_price', 10, 2); // Base price for this room type

            $table->decimal('extra_person_charge', 10, 2)->nullable(); // Charge for extra person
            $table->decimal('extra_bed_charge', 10, 2)->nullable(); // Charge for extra bed

            $table->integer('max_occupancy'); // Maximum number of guests allowed
            $table->string('bed_type'); // Type of bed, e.g., King, Queen, Twin
            $table->boolean('is_accessible')->default(false); // Accessibility flag for guests with disabilities
            $table->json('amenities')->nullable(); // JSON field for amenities
            $table->string('size_type')->nullable(); // Size of the room, e.g., in square feet or meters
            $table->string('view')->nullable(); // Type of view from the room, e.g., sea view, garden view

            $table->timestamps();

            // Foreign key reference to the properties table
            $table->foreign('property_id')->references('id')->on('properties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
