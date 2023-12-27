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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2); // Percentage or fixed amount
            $table->enum('type', ['percentage', 'fixed']); // Percentage of the bill or a fixed amount
            $table->json('applicable_services')->nullable(); // JSON column to store services where the tax is applicable
            $table->boolean('is_global')->default(false); // Indicates if the tax is applicable globally or to specific services
            $table->boolean('is_active')->default(true); // Enable or disable a tax without deleting it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};

