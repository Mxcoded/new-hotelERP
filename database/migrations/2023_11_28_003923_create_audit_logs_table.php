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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable'); // Polymorphic relation to handle different types of records
            $table->string('action'); // E.g., 'created', 'updated', 'cancelled'
            $table->text('description')->nullable(); // Detailed description of the action
            $table->unsignedBigInteger('performed_by'); // User who performed the action
            $table->string('ip_address')->nullable(); //Store user Ip address
            $table->timestamps();

            $table->foreign('performed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
