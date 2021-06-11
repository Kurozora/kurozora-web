<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\MangaCharacter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaCharacterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MangaCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('manga_id');
            $table->unsignedBigInteger('character_id');
            $table->string('role');
            $table->timestamps();
        });

        Schema::table(MangaCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['manga_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MangaCharacter::TABLE_NAME);
    }
}
