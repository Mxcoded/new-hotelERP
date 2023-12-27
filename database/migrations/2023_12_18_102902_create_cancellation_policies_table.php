<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCancellationPoliciesTable extends Migration
{
    public function up()
    {
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('cancellation_deadline')->comment('In hours before check-in');
            $table->decimal('cancellation_fee', 10, 2);
            $table->boolean('is_refundable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cancellation_policies');
    }
};
