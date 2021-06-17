<?php

use App\Models\Anime;
use App\Models\Source;
use App\Models\MediaType;
use App\Models\Status;
use App\Models\TvRating;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('anidb_id')->unique()->nullable();
            $table->unsignedInteger('anilist_id')->unique()->nullable();
            $table->string('imdb_id')->unique()->nullable();
            $table->unsignedInteger('kitsu_id')->unique()->nullable();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('notify_id')->unique()->nullable();
            $table->unsignedInteger('syoboi_id')->nullable();
            $table->unsignedInteger('trakt_id')->nullable();
            $table->unsignedInteger('tvdb_id')->nullable();
            $table->string('slug');
            $table->string('original_title');
            $table->json('synonym_titles')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedBigInteger('media_type_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);
            $table->integer('rating_count')->default(0);
            $table->double('average_rating')->default(0.0);
            $table->string('video_url')->nullable();
            $table->date('first_aired')->nullable();
            $table->date('last_aired')->nullable();
            $table->unsignedMediumInteger('runtime')->default(0);
            $table->time('air_time')->nullable();
            $table->unsignedTinyInteger('air_day')->nullable();
            $table->unsignedTinyInteger('air_season')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->string('copyright')->nullable();
            $table->timestamps();
        });

        Schema::table(Anime::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['slug']);

            // Set foreign key constraints
            $table->foreign('tv_rating_id')->references('id')->on(TvRating::TABLE_NAME)->onDelete('set null');
            $table->foreign('media_type_id')->references('id')->on(MediaType::TABLE_NAME)->onDelete('set null');
            $table->foreign('source_id')->references('id')->on(Source::TABLE_NAME)->onDelete('set null');
            $table->foreign('status_id')->references('id')->on(Status::TABLE_NAME)->onDelete('set null');
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
