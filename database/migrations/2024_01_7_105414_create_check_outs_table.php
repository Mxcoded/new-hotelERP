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
        Schema::create('check_outs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id')->nullable(); // Link to reservation
            $table->unsignedBigInteger('check_in_id')->nullable(); // Link to check-in
            $table->unsignedBigInteger('guest_id'); // Direct link to the guest
            $table->unsignedBigInteger('room_id'); // Room that was occupied
            $table->dateTime('check_out_time'); // Actual check-out time
            $table->decimal('total_bill', 10, 2); // Total bill amount
            $table->decimal('amount_paid', 10, 2); // Amount paid by the guest
            $table->decimal('outstanding_balance', 10, 2); // Outstanding balance, if any
            $table->enum('payment_status', ['paid', 'partially_paid', 'unpaid']); // Payment status
            $table->text('notes')->nullable(); // Any additional notes
            $table->boolean('late_check_out')->default(false); // Indicator for late check-out
            $table->timestamps();

            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('check_in_id')->references('id')->on('check_ins')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_outs');
    }
};
