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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->string('occupation')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('vip')->default(false);
            $table->string('contact_type')->nullable(); // e.g., Phone, Email
            $table->string('email')->unique()->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('address')->nullable();
            $table->string('identity_type')->nullable(); // e.g., Passport, Driving License
            $table->string('identity_id')->nullable(); // ID number of the identity document
            $table->string('front_id_image')->nullable(); // URL or path to the front ID image
            $table->string('back_id_image')->nullable(); // URL or path to the back ID image
            $table->string('guest_image')->nullable(); // URL or path to the guest's image
            $table->json('preferences')->nullable(); // Stores guest preferences
            // New financial columns
            $table->decimal('total_balance', 10, 2)->default(0); // Tracks the overall financial balance
            $table->decimal('credit_limit', 10, 2)->default(0); // Optional: A credit limit for the guest
            $table->unsignedBigInteger('loyalty_program_id')->nullable();
            $table->timestamps();

            $table->foreign('loyalty_program_id')->references('id')->on('loyalty_programs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
