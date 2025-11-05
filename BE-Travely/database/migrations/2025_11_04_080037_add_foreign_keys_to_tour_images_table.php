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
        Schema::table('tour_images', function (Blueprint $table) {
            $table->foreign(['tourID'], 'tour_images_ibfk_1')->references(['tourID'])->on('tour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_images', function (Blueprint $table) {
            $table->dropForeign('tour_images_ibfk_1');
        });
    }
};
