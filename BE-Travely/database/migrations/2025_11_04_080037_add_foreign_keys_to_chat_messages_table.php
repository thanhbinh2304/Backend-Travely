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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign(['conversation_id'], 'chat_messages_ibfk_1')->references(['conversation_id'])->on('chat_conversations');
            $table->foreign(['parent_message_id'], 'chat_messages_ibfk_3')->references(['message_id'])->on('chat_messages');
            $table->foreign(['sender_id'], 'chat_messages_ibfk_2')->references(['userID'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign('chat_messages_ibfk_1');
            $table->dropForeign('chat_messages_ibfk_3');
            $table->dropForeign('chat_messages_ibfk_2');
        });
    }
};
