<?php

use App\Models\Language;
use App\Models\Manga;
use App\Models\MangaTranslation;
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
        Schema::create(MangaTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('manga_id');
            $table->string('locale', 2);
            $table->string('title', 280);
            $table->text('synopsis')->nullable();
            $table->string('tagline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MangaTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['manga_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('manga_id')
                ->references('id')
                ->on(Manga::TABLE_NAME)
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
        Schema::dropIfExists(MangaTranslation::TABLE_NAME);
    }
};
