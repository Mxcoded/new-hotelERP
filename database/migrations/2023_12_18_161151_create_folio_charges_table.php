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
        Schema::create('folio_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('folios');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('date_incurred');
            $table->string('charge_type'); // e.g., 'Room Charge', 'Food and Beverage', 'Amenity', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_charges');
    }
};
