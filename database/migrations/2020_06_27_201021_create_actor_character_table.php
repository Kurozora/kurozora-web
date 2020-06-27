<?php

use App\Actor;
use App\ActorCharacter;
use App\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorCharacterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ActorCharacter::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('character_id');
        });

        Schema::table(ActorCharacter::TABLE_NAME, function(Blueprint $table) {
            $table->unique(['actor_id', 'character_id']);
            $table->foreign('actor_id')->references('id')->on(Actor::TABLE_NAME)->onDelete('cascade');
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
        Schema::dropIfExists(ActorCharacter::TABLE_NAME);
    }
}
