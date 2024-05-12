<?php

use App\Models\Game;
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
        Schema::create(Game::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->unsignedInteger('igdb_id')->unique()->nullable();
            $table->string('igdb_slug')->unique()->nullable();
            $table->string('slug', 280);
            $table->string('original_title', 280);
            $table->json('synonym_titles')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('media_type_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedMediumInteger('duration')->default(0);
            $table->unsignedTinyInteger('publication_day')->nullable();
            $table->unsignedTinyInteger('publication_season')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->string('copyright')->nullable();
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('edition_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->date('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Game::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('is_nsfw');
            $table->index('rank_total');
            $table->index('published_at');
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
            $table->foreign('parent_id')
                ->references('id')
                ->on(Game::TABLE_NAME)
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
        Schema::dropIfExists(Game::TABLE_NAME);
    }
};
