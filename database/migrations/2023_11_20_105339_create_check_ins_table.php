<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('cascade');
            $table->dateTime('check_in_time');
            $table->date('check_in_date')->nullable(); // Date of check-in
            $table->date('check_out_date')->nullable(); // Expected check-out date
            $table->text('notes')->nullable(); // Any notes or comments
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('status')->default('checked-in'); // Status of the check-in
            $table->unsignedBigInteger('checked_in_by')->nullable(); // Staff member who handled the check-in
            $table->dateTime('expected_check_out_time')->nullable(); // Expected time for check-out
            $table->string('room_key_card')->nullable(); // Key card number or identifier
            $table->boolean('is_group_check_in')->default(false); // Flag for group check-ins
            $table->json('additional_guests')->nullable(); // Information about additional guests if any

            // Foreign key constraints
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
