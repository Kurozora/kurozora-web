<?php

use App\Models\Manga;
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
        Schema::create(Manga::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('anidb_id')->unique()->nullable();
            $table->unsignedInteger('anilist_id')->unique()->nullable();
            $table->string('animeplanet_id')->nullable();
            $table->unsignedInteger('anisearch_id')->nullable();
            $table->unsignedInteger('kitsu_id')->unique()->nullable();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('slug');
            $table->string('original_title');
            $table->json('synonym_titles')->nullable();
            $table->string('copyright')->nullable();
            $table->unsignedMediumInteger('duration')->default(0);
            $table->time('publication_time')->nullable();
            $table->unsignedTinyInteger('publication_day')->nullable();
            $table->unsignedTinyInteger('publication_season')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedBigInteger('media_type_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->integer('volume_count')->default(0);
            $table->integer('page_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Manga::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(Manga::TABLE_NAME);
    }
};
