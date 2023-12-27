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
        // Schema::create('rates', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('room_type_id');
        //     $table->decimal('price', 8, 2);
        //     $table->timestamps();

        //     $table->foreign('room_type_id')->references('id')->on('room_types');
        // });

        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types');
            $table->foreignId('rate_type_id')->constrained('rate_types');
            $table->decimal('price', 8, 2);
            $table->date('start_date')->nullable(); // Start date for the rate
            $table->date('end_date')->nullable(); // End date for the rate
            $table->json('conditions')->nullable(); // Conditions for rate applicability
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
