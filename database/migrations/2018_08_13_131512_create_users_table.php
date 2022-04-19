<?php

use App\Models\User;
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
        Schema::create(User::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('love_reacter_id')->nullable();
            $table->string('siwa_id')->nullable();
            $table->string('slug');
            $table->string('username', 50)->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('email_suspended')->default(false);
            $table->string('password')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->rememberToken();
            $table->text('biography')->nullable();
            $table->json('settings');
            $table->timestamp('last_anime_import_at')->nullable();
            $table->timestamp('last_manga_import_at')->nullable();
            $table->timestamps();
        });

        Schema::table(User::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['slug']);

            // Set foreign key constraints
            $table->foreign('love_reacter_id')->references('id')->on('love_reacters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(User::TABLE_NAME);
    }
};
