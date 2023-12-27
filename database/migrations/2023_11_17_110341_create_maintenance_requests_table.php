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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->nullable(); // Make nullable for facility maintenance
            $table->unsignedBigInteger('facility_id')->nullable(); // Add for facility maintenance
            $table->string('issue');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            $table->string('urgency'); // e.g., low, medium, high
            $table->dateTime('started_at')->nullable()->default(now());
            $table->dateTime('finished_at')->nullable();

            $table->timestamps();

            // Foreign key reference to the rooms table
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');

            // Foreign key reference to the facilities table
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
