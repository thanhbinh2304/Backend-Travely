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
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->foreign(['user_id'], 'chat_conversations_ibfk_1')->references(['userID'])->on('users');
            $table->foreign(['bookingID'], 'chat_conversations_ibfk_3')->references(['bookingID'])->on('booking');
            $table->foreign(['admin_id'], 'chat_conversations_ibfk_2')->references(['userID'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropForeign('chat_conversations_ibfk_1');
            $table->dropForeign('chat_conversations_ibfk_3');
            $table->dropForeign('chat_conversations_ibfk_2');
        });
    }
};
