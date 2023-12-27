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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_id');
            $table->decimal('total_amount', 8, 2);
            $table->date('issue_date');
            $table->enum('status', ['unpaid', 'paid', 'cancelled']);
            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
