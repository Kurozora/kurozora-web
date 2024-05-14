<?php

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Language;
use App\Models\Person;
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
        Schema::create(AnimeCast::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('character_id');
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('cast_role_id');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(AnimeCast::TABLE_NAME, function(Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['person_id', 'character_id', 'anime_id', 'cast_role_id', 'language_id'], 'anime_cast_person_character_language_unique');

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
            $table->foreign('anime_id')
                ->references('id')
                ->on(Anime::TABLE_NAME)
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
        Schema::dropIfExists(AnimeCast::TABLE_NAME);
    }
};
