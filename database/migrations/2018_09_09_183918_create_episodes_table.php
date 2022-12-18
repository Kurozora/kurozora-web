<?php

use App\Models\Episode;
use App\Models\Season;
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
        Schema::create(Episode::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('next_episode_id')->nullable();
            $table->unsignedInteger('previous_episode_id')->nullable();
            $table->unsignedBigInteger('season_id');
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedInteger('number');
            $table->unsignedInteger('number_total');
            $table->unsignedMediumInteger('duration')->default(0);
            $table->boolean('is_filler')->default(false);
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_special')->default(false);
            $table->boolean('is_premiere')->default(false);
            $table->boolean('is_finale')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('watch_count')->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Episode::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['season_id', 'number']);

            // Set foreign key constraints
            $table->foreign('next_episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('set null');
            $table->foreign('previous_episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('set null');
            $table->foreign('season_id')->references('id')->on(Season::TABLE_NAME)->onDelete('cascade');
            $table->foreign('tv_rating_id')->references('id')->on(TvRating::TABLE_NAME)->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Episode::TABLE_NAME);
    }
};
