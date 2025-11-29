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
            $table->text('images')->nullable()->after('comment');
            $table->enum('status', ['pending', 'approved', 'hidden'])->default('pending')->after('images');
            $table->timestamp('updated_at')->nullable()->after('timestamp');
            $table->timestamp('approved_at')->nullable()->after('updated_at');
            $table->char('approved_by', 36)->nullable()->after('approved_at');
            $table->boolean('is_verified_purchase')->default(false)->after('approved_by');
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
            $table->dropColumn(['images', 'status', 'updated_at', 'approved_at', 'approved_by', 'is_verified_purchase']);
        });
    }
};
