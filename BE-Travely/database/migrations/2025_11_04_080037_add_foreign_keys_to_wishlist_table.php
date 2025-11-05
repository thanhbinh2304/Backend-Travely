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
        Schema::table('wishlist', function (Blueprint $table) {
            $table->foreign(['userID'], 'wishlist_ibfk_1')->references(['userID'])->on('users');
            $table->foreign(['tourID'], 'wishlist_ibfk_2')->references(['tourID'])->on('tour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wishlist', function (Blueprint $table) {
            $table->dropForeign('wishlist_ibfk_1');
            $table->dropForeign('wishlist_ibfk_2');
        });
    }
};
