<?php

use App\Models\CastRole;
use App\Models\Character;
use App\Models\Manga;
use App\Models\MangaCast;
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
        Schema::create(MangaCast::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('manga_id');
            $table->unsignedBigInteger('character_id');
            $table->unsignedBigInteger('cast_role_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MangaCast::TABLE_NAME, function(Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['character_id', 'manga_id', 'cast_role_id'], 'manga_cast_character_manga_role_unique');

            // Set foreign key constraints
            $table->foreign('character_id')
                ->references('id')
                ->on(Character::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('manga_id')
                ->references('id')
                ->on(Manga::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('cast_role_id')
                ->references('id')
                ->on(CastRole::TABLE_NAME)
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
        Schema::dropIfExists(MangaCast::TABLE_NAME);
    }
};
