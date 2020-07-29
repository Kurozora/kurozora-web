<?php

use App\ActorCharacter;
use App\ActorCharacterAnime;
use App\Anime;
use App\Enums\CastRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorCharacterAnimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ActorCharacterAnime::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('actor_character_id');
            $table->unsignedBigInteger('anime_id');
            $table->unsignedTinyInteger('cast_role')->default(CastRole::SupportingCharacter);
            $table->timestamps();
        });

        Schema::table(ActorCharacterAnime::TABLE_NAME, function(Blueprint $table) {
            // Set unique index constraints
            $table->unique(['actor_character_id', 'anime_id']);

            // Set foreign key constraints
            $table->foreign('actor_character_id')->references('id')->on(ActorCharacter::TABLE_NAME)->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ActorCharacterAnime::TABLE_NAME);
    }
}
