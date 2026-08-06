<?php

use App\Models\CastRole;
use App\Models\Character;
use App\Models\Game;
use App\Models\GameCast;
use App\Models\Language;
use App\Models\Person;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create(GameCast::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('character_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('cast_role_id');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(GameCast::TABLE_NAME, function(Blueprint $table) {
            // Set index key constraints
            $table->index(['game_id', 'deleted_at', 'id']);

            // Set unique key constraints
            $table->unique(
                [DB::raw('(COALESCE(person_id, 0))'), DB::raw('(COALESCE(character_id, 0))'), 'game_id', 'cast_role_id', DB::raw('(COALESCE(language_id, 0))')],
                'game_cast_person_character_language_unique'
            );

            // Set foreign key constraints
            $table->foreign('person_id')
                ->references('id')
                ->on(Person::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('character_id')
                ->references('id')
                ->on(Character::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('game_id')
                ->references('id')
                ->on(Game::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('cast_role_id')
                ->references('id')
                ->on(CastRole::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('language_id')
                ->references('id')
                ->on(Language::TABLE_NAME)
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
        Schema::dropIfExists(GameCast::TABLE_NAME);
    }
};
