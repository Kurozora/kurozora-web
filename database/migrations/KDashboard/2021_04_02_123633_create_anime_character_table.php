<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\AnimeCharacter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeCharacterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('character_id');
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('language_id');
            $table->string('role');
            $table->timestamps();
        });

        Schema::table(AnimeCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['anime_id', 'character_id', 'people_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeCharacter::TABLE_NAME);
    }
}
