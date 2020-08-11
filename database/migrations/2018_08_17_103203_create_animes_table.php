<?php

use App\Anime;
use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\WatchRating;
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
            $table->unsignedInteger('anidb_id')->nullable();
            $table->unsignedInteger('anilist_id')->nullable();
            $table->string('imdb_id')->nullable();
            $table->unsignedInteger('kitsu_id')->nullable();
            $table->unsignedInteger('mal_id')->nullable();
            $table->unsignedInteger('tvdb_id')->nullable();
            $table->string('slug');
            $table->string('title')->default('Unknown title');
            $table->string('tagline')->nullable();
            $table->mediumText('synopsis')->nullable();
            $table->integer('type')->default(AnimeType::Unknown);
            $table->integer('watch_rating')->default(WatchRating::Unknown);
            $table->integer('adaptation_source')->default(AnimeSource::Unknown);
            $table->string('network')->nullable();
            $table->string('producer')->nullable();
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);
            $table->integer('rating_count')->default(0);
            $table->double('average_rating')->default(0.0);
            $table->string('video_url')->nullable();
            $table->date('first_aired')->nullable();
            $table->date('last_aired')->nullable();
            $table->unsignedTinyInteger('runtime')->default(0);
            $table->integer('air_status')->default(AnimeStatus::TBA);
            $table->time('air_time')->nullable();
            $table->unsignedInteger('air_day')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->string('copyright')->nullable();

            // Flags for fetched data
            $table->boolean('fetched_actors')->default(false);
            $table->boolean('fetched_base_episodes')->default(false);
            $table->boolean('fetched_images')->default(false);
            $table->boolean('fetched_details')->default(false);

            $table->timestamps();
        });

        Schema::table(Anime::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['slug']);
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
