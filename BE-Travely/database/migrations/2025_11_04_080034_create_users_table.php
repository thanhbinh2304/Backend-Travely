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
        Schema::create('users', function (Blueprint $table) {
            $table->char('userID', 36)->primary();
            $table->string('userName', 32);
            $table->text('passWord');
            $table->string('phoneNumber', 15)->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable()->unique('email');
            $table->bigInteger('role_id')->index('role_id');
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('updated_by');
            $table->text('refresh_token')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('verification_token')->nullable();
            $table->timestamp('verification_token_expires_at')->nullable();
            $table->string('google_id')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_admin')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
