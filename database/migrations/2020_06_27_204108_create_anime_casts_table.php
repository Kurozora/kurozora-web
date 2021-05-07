<?php

use App\Models\Actor;
use App\Models\AnimeCast;
use App\Models\Anime;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeCastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeCast::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('character_id');
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('cast_role_id')->nullable();
            $table->unsignedBigInteger('language_id');
            $table->timestamps();
        });

        Schema::table(AnimeCast::TABLE_NAME, function(Blueprint $table) {
            // Set unique index constraints
            $table->unique(['actor_id', 'character_id', 'anime_id']);

            // Set foreign key constraints
            $table->foreign('actor_id')->references('id')->on(Actor::TABLE_NAME)->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on(Character::TABLE_NAME)->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('cast_role_id')->references('id')->on(CastRole::TABLE_NAME)->onDelete('set null');
            $table->foreign('language_id')->references('id')->on(Language::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeCast::TABLE_NAME);
    }
}
