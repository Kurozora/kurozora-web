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
            $table->string('locale', 2)->index();
            $table->string('title');
            $table->text('synopsis')->nullable();
            $table->string('tagline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(AnimeTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['anime_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('locale')->references('code')->on(Language::TABLE_NAME);
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
