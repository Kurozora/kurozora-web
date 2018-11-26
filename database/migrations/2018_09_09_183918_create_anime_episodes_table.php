<?php

use App\Anime;
use App\AnimeEpisode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimeEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeEpisode::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('anime_id')->unsigned();
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');

            $table->boolean('verified')->default(false);
            $table->integer('season')->unsigned()->default(1);
            $table->integer('number')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->timestamp('first_aired')->nullable();
            $table->text('overview')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeEpisode::TABLE_NAME);
    }
}
