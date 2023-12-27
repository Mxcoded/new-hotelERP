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
            $table->unsignedBigInteger('reservation_id')->nullable(); // Making this nullable
            $table->unsignedBigInteger('check_in_id')->nullable(); // New field to link directly to check-in
            $table->dateTime('check_out_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('check_in_id')->references('id')->on('check_ins')->onDelete('set null');
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
