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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->char('message_id', 36)->primary();
            $table->char('conversation_id', 36)->index('conversation_id');
            $table->char('sender_id', 36)->index('sender_id');
            $table->char('parent_message_id', 36)->index('parent_message_id');
            $table->text('message_text');
            $table->enum('message_type', ['text', 'image', 'file', 'voice', 'system'])->default('text');
            $table->string('attachment_url', 500)->nullable();
            $table->string('attachment_name')->nullable();
            $table->bigInteger('attachment_size')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable()->useCurrent();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
