<?php

use App\AnimeEpisode;
use App\User;
use App\UserWatchedEpisode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWatchedEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(UserWatchedEpisode::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');

            $table->integer('episode_id')->unsigned();
            $table->foreign('episode_id')->references('id')->on(AnimeEpisode::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(UserWatchedEpisode::TABLE_NAME);
    }
}
