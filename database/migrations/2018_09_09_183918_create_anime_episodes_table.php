<?php

use App\AnimeEpisode;
use App\AnimeSeason;
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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('season_id');
            $table->unsignedInteger('number');
            $table->string('title');
            $table->text('overview')->nullable();
            $table->string('preview_image')->nullable();
            $table->dateTime('first_aired')->nullable();
            $table->unsignedTinyInteger('duration')->default(0);
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });

        Schema::table(AnimeEpisode::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('season_id')->references('id')->on(AnimeSeason::TABLE_NAME)->onDelete('cascade');
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
