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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique()->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('login_access')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            // Add other custom columns here
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
