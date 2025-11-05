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
        Schema::table('review', function (Blueprint $table) {
            $table->foreign(['tourID'], 'review_ibfk_1')->references(['tourID'])->on('tour');
            $table->foreign(['userID'], 'review_ibfk_2')->references(['userID'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review', function (Blueprint $table) {
            $table->dropForeign('review_ibfk_1');
            $table->dropForeign('review_ibfk_2');
        });
    }
};
