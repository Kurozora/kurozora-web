<?php

use App\Actor;
use App\ActorAnimeCharacter;
use App\Anime;
use App\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorAnimeCharacterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ActorAnimeCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedInteger('actor_id');
            $table->unsignedInteger('anime_id');
            $table->unsignedBigInteger('character_id');
        });

        Schema::table(ActorAnimeCharacter::TABLE_NAME, function(Blueprint $table) {
            $table->unique(['actor_id', 'anime_id', 'character_id']);
            $table->foreign('actor_id')->references('id')->on(Actor::TABLE_NAME)->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on(Character::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ActorAnimeCharacter::TABLE_NAME);
    }
}
