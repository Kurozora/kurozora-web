<?php

use App\Models\Genre;
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
        Schema::create(Genre::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('background_color_1')->default('#353A50');
            $table->string('background_color_2')->default('#454F63');
            $table->string('text_color_1')->default('#EEEEEE');
            $table->string('text_color_2')->default('#AFAFAF');
            $table->text('description')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Genre::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['mal_id']);
            $table->unique(['slug']);

            // Set foreign key constraints
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
        Schema::dropIfExists(Genre::TABLE_NAME);
    }
};
