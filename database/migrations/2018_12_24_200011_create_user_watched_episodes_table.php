<?php

use App\Models\Episode;
use App\Models\User;
use App\Models\UserWatchedEpisode;
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
    public function up(): void
    {
        Schema::create(UserWatchedEpisode::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('episode_id');
            $table->timestamps();
        });

        Schema::table(UserWatchedEpisode::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
            $table->foreign('episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(UserWatchedEpisode::TABLE_NAME);
    }
};
