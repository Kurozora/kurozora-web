<?php

use App\Models\Theme;
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
        Schema::create(Theme::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->nullable();
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('color')->default('#ffffff');
            $table->text('description')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Theme::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['mal_id']);

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
        Schema::dropIfExists(Theme::TABLE_NAME);
    }
};
