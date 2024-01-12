<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations');
            $table->foreignId('guest_id')->constrained('guests');
            $table->foreignId('check_in_id')->nullable()->constrained('check_ins')->onDelete('set null');
            $table->date('date_created');
            $table->decimal('total_charges', 10, 2)->default(0);
            $table->decimal('total_payments', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('status')->default('Open');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};
