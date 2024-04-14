<?php

use App\Models\Anime;
use App\Models\AnimeTranslation;
use App\Models\Language;
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
        Schema::create(AnimeTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->string('locale', 2);
            $table->string('title', 280);
            $table->text('synopsis')->nullable();
            $table->string('tagline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(AnimeTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['anime_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('anime_id')
                ->references('id')
                ->on(Anime::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('locale')
                ->references('code')
                ->on(Language::TABLE_NAME)
                ->cascadeOnDelete()
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
        Schema::dropIfExists(AnimeTranslation::TABLE_NAME);
    }
};
