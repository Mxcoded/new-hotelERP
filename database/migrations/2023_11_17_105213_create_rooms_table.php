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
        // Schema::create('rooms', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('property_id');
        //     $table->unsignedBigInteger('room_type_id');
        //     $table->string('number');
        //     $table->text('description')->nullable();
        //     $table->timestamps();

        //     $table->foreign('property_id')->references('id')->on('properties');
        //     $table->foreign('room_type_id')->references('id')->on('room_types');
        // });

        // Schema::create('rooms', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('room_type_id')->nullable()->constrained('room_types')->onDelete('set null');
        //     $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
        //     $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
        //     $table->string('number'); // Room number
        //     $table->text('description')->nullable();
        //     $table->boolean('is_available')->default(true);

        //     // Additional details like view, special features can be added here
        //     $table->timestamps();
        // });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->onDelete('set null');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->string('number'); // Room number
            $table->text('description')->nullable();
            $table->string('status')->default('available'); // Default status can be 'available'
            $table->decimal('base_price', 10, 2)->nullable(); // Base rate for the room
            $table->json('features')->nullable(); // Additional features as a JSON field

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
