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
        Schema::create('checkout', function (Blueprint $table) {
            $table->bigInteger('checkoutID', true);
            $table->bigInteger('bookingID')->index('bookingID');
            $table->string('paymentMethod', 50);
            $table->dateTime('paymentDate')->useCurrent();
            $table->decimal('amount', 10);
            $table->enum('paymentStatus', ['Pending', 'Completed', 'Failed', 'Refunded'])->default('Pending');
            $table->string('transactionID')->comment('Mã GD từ cổng thanh toán
');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkout');
    }
};
