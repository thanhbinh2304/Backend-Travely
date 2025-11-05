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
        Schema::table('booking', function (Blueprint $table) {
            $table->foreign(['userID'], 'booking_ibfk_1')->references(['userID'])->on('users');
            $table->foreign(['tourID'], 'booking_ibfk_2')->references(['tourID'])->on('tour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign('booking_ibfk_1');
            $table->dropForeign('booking_ibfk_2');
        });
    }
};
