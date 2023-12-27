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
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->dateTime('check_in_time')->nullable(); // Actual check-in time
            $table->dateTime('check_out_time')->nullable(); // Actual check-out time
            $table->string('status')->default('reserved'); // Status of the allocation, e.g., 'reserved', 'occupied', 'vacated'
            $table->text('notes')->nullable(); // Any notes related to the room allocation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
    }
};
