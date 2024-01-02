<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('folio_id')->constrained('folios')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->dateTime('payment_date');
            $table->string('transaction_id')->nullable()->default(null);
            $table->string('status')->default('completed'); // e.g., 'completed', 'pending', 'refunded'
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('notes')->nullable()->default(null);
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('reservation_id');
            $table->index('folio_id');
            $table->index('guest_id');
            $table->index('property_id');
            $table->index('processed_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}

