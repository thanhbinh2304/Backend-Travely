<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->bigInteger('bookingID', true);
            $table->bigInteger('tourID')->index('tourID');
            $table->char('userID', 36)->index('userID');
            $table->dateTime('bookingDate')->useCurrent();
            $table->integer('numAdults');
            $table->integer('numChildren');
            $table->decimal('totalPrice', 10);
            $table->enum('paymentStatus', ['pending', 'paid', 'refunded'])->default('pending');
            $table->enum('bookingStatus', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->text('specialRequests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking');
    }
};
