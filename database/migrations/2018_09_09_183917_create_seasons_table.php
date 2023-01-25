<?php

use App\Models\Anime;
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
        Schema::create(Season::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->integer('number');
            $table->boolean('is_nsfw')->default(false);
            $table->integer('view_count')->default(0);
            $table->dateTime('first_aired')->nullable();
            $table->dateTime('last_aired')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Season::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['anime_id', 'number']);

            // Set foreign key constraints
            $table->foreign('anime_id')
                ->references('id')
                ->on(Anime::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('tv_rating_id')
                ->references('id')
                ->on(TvRating::TABLE_NAME)
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
        Schema::dropIfExists(Season::TABLE_NAME);
    }
};
