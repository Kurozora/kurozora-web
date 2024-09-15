<?php

use App\Models\Studio;
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
        Schema::create(Studio::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('successor_id')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->unsignedInteger('mal_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->string('slug');
            $table->string('name');
            $table->string('japanese_name')->nullable();
            $table->json('alternative_names')->nullable();
            $table->mediumText('about')->nullable();
            $table->mediumText('address')->nullable();
            $table->json('social_urls')->nullable();
            $table->json('website_urls')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('view_count')->default(0);
            $table->date('founded_at')->nullable();
            $table->date('defunct_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Studio::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('rank_total');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['mal_id', 'type', 'name']);

            // Set foreign key constraints
            $table->foreign('successor_id')
                ->references('id')
                ->on(Studio::TABLE_NAME)
                ->nullOnDelete()
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
        Schema::dropIfExists(Studio::TABLE_NAME);
    }
};
