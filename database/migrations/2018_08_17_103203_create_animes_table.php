<?php

use App\Anime;
use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Anime::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->default('Unknown title');
            $table->string('tagline')->nullable();
            $table->string('video_url')->nullable();
            $table->string('slug')->nullable();
            $table->string('network')->nullable();
            $table->integer('status')->default(AnimeStatus::TBA);
            $table->integer('type')->default(AnimeType::Unknown);
            $table->integer('source')->default(AnimeSource::Unknown);
            $table->boolean('nsfw')->default(false);
            $table->unsignedInteger('anidb_id')->nullable();
            $table->unsignedInteger('anilist_id')->nullable();
            $table->string('imdb_id')->nullable();
            $table->unsignedInteger('kitsu_id')->nullable();
            $table->unsignedInteger('mal_id')->nullable();
            $table->unsignedInteger('tvdb_id')->nullable();
            $table->mediumText('synopsis')->nullable();
            $table->unsignedTinyInteger('runtime')->default(0);
            $table->integer('watch_rating')->nullable();
            $table->float('average_rating')->default(0.0);
            $table->integer('rating_count')->default(0);
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);
            $table->date('first_aired')->nullable();
            $table->date('last_aired')->nullable();
            $table->time('air_time')->nullable();
            $table->unsignedInteger('air_day')->nullable();
            $table->string('copyright')->nullable();

            // Flags for fetched data
            $table->boolean('fetched_actors')->default(false);
            $table->boolean('fetched_base_episodes')->default(false);
            $table->boolean('fetched_images')->default(false);
            $table->boolean('fetched_details')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Anime::TABLE_NAME);
    }
}
