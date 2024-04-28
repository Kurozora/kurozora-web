<?php

use App\Models\Language;
use App\Models\Song;
use App\Models\SongTranslation;
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
        Schema::create(SongTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('song_id');
            $table->string('locale', 2);
            $table->string('title', 280)->nullable();
            $table->text('lyrics')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(SongTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['song_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('song_id')
                ->references('id')
                ->on(Song::TABLE_NAME)
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
        Schema::dropIfExists(SongTranslation::TABLE_NAME);
    }
};
