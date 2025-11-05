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
        Schema::create('tour', function (Blueprint $table) {
            $table->bigInteger('tourID', true);
            $table->string('title');
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('priceAdult', 10);
            $table->decimal('priceChild', 10);
            $table->string('destination');
            $table->boolean('availability')->default(true)->comment('1: còn chỗ');
            $table->date('startDate');
            $table->date('endDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour');
    }
};
