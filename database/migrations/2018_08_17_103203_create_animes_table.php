<?php

use App\Anime;
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
        Schema::create('anime', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('title')->default('Unknown title');
            $table->string('slug')->nullable();
            $table->string('network')->nullable();
            $table->string('status')->default(Anime::ANIME_STATUS_TBA);
            $table->string('cached_poster')->nullable();
            $table->string('cached_poster_thumbnail')->nullable();
            $table->string('cached_background')->nullable();
            $table->string('cached_background_thumbnail')->nullable();
            $table->integer('type')->default(Anime::ANIME_TYPE_UNDEFINED);
            $table->boolean('nsfw')->default(false);
            $table->integer('tvdb_id')->nullable()->unsigned();
            $table->string('imdb_id')->nullable();
            $table->mediumText('synopsis')->nullable();
            $table->tinyInteger('runtime')->nullable()->unsigned();
            $table->string('watch_rating')->nullable();
            $table->float('average_rating')->default(0.0);
            $table->integer('rating_count')->default(0);
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);

            // Flags for fetched data
            $table->boolean('fetched_actors')->default(false);
            $table->boolean('fetched_base_episodes')->default(false);
            $table->boolean('fetched_images')->default(false);
            $table->boolean('fetched_details')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anime');
    }
}
