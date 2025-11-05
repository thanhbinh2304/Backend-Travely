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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->char('conversation_id', 36)->primary();
            $table->char('user_id', 36)->index('user_id');
            $table->char('admin_id', 36)->index('admin_id');
            $table->bigInteger('bookingID')->index('bookingID');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_message_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->boolean('is_pinned')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_conversations');
    }
};
