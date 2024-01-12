<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id(); // Automatically creates an auto-incrementing 'id' column
            $table->foreignId('guest_id')->constrained('guests');
            $table->foreignId('room_id')->constrained('rooms');
            $table->date('reservation_date');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_guests');
            $table->integer('total_nights')->unsigned();
            $table->decimal('price', 10, 2);
            $table->string('status');
            $table->string('payment_method');
            $table->string('payment_status');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2)->default(0);
            $table->text('special_requests')->nullable();
            $table->foreignId('cancellation_policy_id')->constrained('cancellation_policies');
            $table->foreignId('folio_id')->nullable()->constrained('folios')->change();
            $table->foreignId('property_id')->constrained('properties');
            $table->timestamps(); // Creates 'created_at' and 'updated_at' columns

            // New fields
            $table->boolean('is_early_checkin')->default(false); // Indicates if the reservation involved an early check-in
            $table->boolean('is_late_checkout')->default(false); // Indicates if the reservation had a late checkout
            $table->dateTime('actual_check_in_time')->nullable(); // The actual time of check-in
            $table->dateTime('actual_check_out_time')->nullable(); // The actual time of check-out

        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
