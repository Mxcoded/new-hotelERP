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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->dateTime('check_in_time');
            $table->text('notes')->nullable();

            // Additional fields that might be relevant
            $table->unsignedBigInteger('guest_id')->nullable(); // In case of direct check-in without reservation
            $table->unsignedBigInteger('room_id')->nullable(); // The room that was assigned
            $table->string('status')->default('checked-in'); // Status of the check-in
            $table->unsignedBigInteger('checked_in_by')->nullable(); // The staff member who handled the check-in

            // Foreign key constraints
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('set null'); // Assuming 'users' is your staff table

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
