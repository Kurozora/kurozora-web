<?php

use App\Models\Game;
use App\Models\GameTranslation;
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
        Schema::create(GameTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('game_id');
            $table->string('locale', 2)->index();
            $table->string('title', 280);
            $table->text('synopsis')->nullable();
            $table->string('tagline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(GameTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['game_id', 'locale']);

            // Set index key constraints
            $table->foreign('game_id')
                ->references('id')
                ->on(Game::TABLE_NAME)
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
        Schema::dropIfExists(GameTranslation::TABLE_NAME);
    }
};
