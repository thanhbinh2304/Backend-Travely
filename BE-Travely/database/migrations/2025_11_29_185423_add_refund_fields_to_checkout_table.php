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
            $table->timestamp('refundDate')->nullable()->after('paymentDate');
            $table->decimal('refundAmount', 15, 2)->nullable()->after('refundDate');
            $table->text('refundReason')->nullable()->after('refundAmount');
            $table->integer('refundBy')->nullable()->after('refundReason');
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
            $table->dropColumn(['refundDate', 'refundAmount', 'refundReason', 'refundBy']);
        });
    }
};
