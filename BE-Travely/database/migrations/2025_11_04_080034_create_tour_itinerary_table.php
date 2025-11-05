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
        Schema::create('tour_itinerary', function (Blueprint $table) {
            $table->bigInteger('itineraryID', true);
            $table->bigInteger('tourID')->index('tourID');
            $table->integer('dayNumber');
            $table->text('activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour_itinerary');
    }
};
