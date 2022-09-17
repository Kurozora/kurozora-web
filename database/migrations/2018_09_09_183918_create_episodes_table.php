<?php

use App\Models\Episode;
use App\Models\Season;
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
            $table->unsignedInteger('number');
            $table->unsignedInteger('number_total');
            $table->unsignedMediumInteger('duration')->default(0);
            $table->dateTime('first_aired')->nullable();
            $table->boolean('is_filler')->default(false);
            $table->string('video_url')->nullable();
            $table->boolean('verified')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('watch_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Episode::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['season_id', 'number']);

            // Set foreign key constraints
            $table->foreign('season_id')->references('id')->on(Season::TABLE_NAME)->onDelete('cascade');
            $table->foreign('next_episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('set null');
            $table->foreign('previous_episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('set null');
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
