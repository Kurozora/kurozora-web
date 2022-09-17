<?php

use App\Models\Character;
use App\Models\CharacterTranslation;
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
        Schema::create(CharacterTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id');
            $table->string('locale', 2)->index();
            $table->string('name');
            $table->text('about')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(CharacterTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['character_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('character_id')->references('id')->on(Character::TABLE_NAME)->onDelete('cascade');
            $table->foreign('locale')->references('code')->on(Language::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(CharacterTranslation::TABLE_NAME);
    }
};
