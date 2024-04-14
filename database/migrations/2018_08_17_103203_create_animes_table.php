<?php

use App\Models\Anime;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
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
        Schema::create(Anime::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('anidb_id')->unique()->nullable();
            $table->unsignedInteger('anilist_id')->unique()->nullable();
            $table->string('animeplanet_id')->nullable();
            $table->unsignedInteger('anisearch_id')->nullable();
            $table->string('filler_id')->nullable();
            $table->string('imdb_id')->unique()->nullable();
            $table->unsignedInteger('kitsu_id')->unique()->nullable();
            $table->unsignedInteger('livechart_id')->nullable();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('notify_id')->unique()->nullable();
            $table->unsignedInteger('syoboi_id')->nullable();
            $table->unsignedInteger('trakt_id')->nullable();
            $table->unsignedInteger('tvdb_id')->nullable();
            $table->string('slug', 280);
            $table->string('original_title', 280);
            $table->json('synonym_titles')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedBigInteger('media_type_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedMediumInteger('duration')->default(0);
            $table->time('air_time')->nullable();
            $table->unsignedTinyInteger('air_day')->nullable();
            $table->unsignedTinyInteger('air_season')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->string('copyright')->nullable();
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('episode_count')->default(0);
            $table->integer('season_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Anime::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('is_nsfw');
            $table->index('rank_total');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);

            // Set foreign key constraints
            $table->foreign('tv_rating_id')
                ->references('id')
                ->on(TvRating::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('media_type_id')
                ->references('id')
                ->on(MediaType::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('source_id')
                ->references('id')
                ->on(Source::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('status_id')
                ->references('id')
                ->on(Status::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Anime::TABLE_NAME);
    }
};
