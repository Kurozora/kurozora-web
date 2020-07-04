<?php

use App\Anime;
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
            $table->increments('id');
            $table->timestamps();

            $table->string('title')->default('Unknown title');
            $table->string('tagline')->nullable();
            $table->string('video_url')->nullable();
            $table->string('slug')->nullable();
            $table->string('network')->nullable();
            $table->unsignedBigInteger('studio_id')->nullable();
            $table->integer('status')->default(AnimeStatus::TBA);
            $table->string('cached_poster')->nullable();
            $table->string('cached_poster_thumbnail')->nullable();
            $table->string('cached_background')->nullable();
            $table->string('cached_background_thumbnail')->nullable();
            $table->integer('type')->default(AnimeType::Unknown);
            $table->boolean('nsfw')->default(false);
            $table->integer('anidb_id')->nullable()->unsigned();
            $table->integer('anilist_id')->nullable()->unsigned();
            $table->string('imdb_id')->nullable();
            $table->integer('kitsu_id')->nullable()->unsigned();
            $table->integer('mal_id')->nullable()->unsigned();
            $table->integer('tvdb_id')->nullable()->unsigned();
            $table->mediumText('synopsis')->nullable();
            $table->tinyInteger('runtime')->nullable()->unsigned();
            $table->integer('watch_rating')->nullable();
            $table->float('average_rating')->default(0.0);
            $table->integer('rating_count')->default(0);
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);
	        $table->date('first_aired')->nullable();
            $table->date('last_aired')->nullable();
	        $table->time('air_time')->nullable();
	        $table->integer('air_day')->nullable()->unsigned();
            $table->string('copyright')->nullable();

            // Flags for fetched data
            $table->boolean('fetched_actors')->default(false);
            $table->boolean('fetched_base_episodes')->default(false);
            $table->boolean('fetched_images')->default(false);
            $table->boolean('fetched_details')->default(false);
        });

        Schema::table(Anime::TABLE_NAME, function(Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('studio_id')->references('id')->on('studios')->onDelete('set null');
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
