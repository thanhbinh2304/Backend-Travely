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
        Schema::table('checkout', function (Blueprint $table) {
            $table->text('paymentData')->nullable()->after('transactionID');
            $table->string('qrCode')->nullable()->after('paymentData');
            $table->timestamp('createdAt')->nullable()->after('qrCode');
            $table->timestamp('updatedAt')->nullable()->after('createdAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkout', function (Blueprint $table) {
            $table->dropColumn(['paymentData', 'qrCode', 'createdAt', 'updatedAt']);
        });
    }
};
