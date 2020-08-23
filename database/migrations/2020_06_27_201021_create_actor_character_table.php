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
            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('character_id');
            $table->timestamps();
        });

        Schema::table(ActorCharacter::TABLE_NAME, function(Blueprint $table) {
            // Set unique index constraints
            $table->unique(['actor_id', 'character_id']);

            // Set foreign key constraints
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
