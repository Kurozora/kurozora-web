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
        Schema::create('animes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('title')->default('Unknown title');
            $table->string('cached_poster')->nullable();
            $table->string('cached_poster_thumbnail')->nullable();
            $table->string('cached_background')->nullable();
            $table->string('cached_background_thumbnail')->nullable();
            $table->integer('type')->default(Anime::ANIME_TYPE_UNDEFINED);
            $table->boolean('nsfw')->default(false);
            $table->integer('tvdb_id')->nullable()->unsigned();
            $table->mediumText('synopsis')->nullable();
            $table->tinyInteger('runtime')->nullable()->unsigned();
            $table->string('watch_rating')->nullable();
            $table->float('average_rating')->default(0.0);
            $table->integer('rating_count')->default(0);

            // Flags for fetched data
            $table->boolean('fetched_poster')->default(false);
            $table->boolean('fetched_poster_thumbnail')->default(false);
            $table->boolean('fetched_background')->default(false);
            $table->boolean('fetched_background_thumbnail')->default(false);
            $table->boolean('fetched_actors')->default(false);
            $table->boolean('fetched_synopsis')->default(false);
            $table->boolean('fetched_runtime')->default(false);
            $table->boolean('fetched_watch_rating')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('animes');
    }
}
