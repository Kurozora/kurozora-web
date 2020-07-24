<?php

use App\Anime;
use App\AnimeRelations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeRelations::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedInteger('anime_id');
            $table->unsignedInteger('related_anime_id');
            $table->integer('type');
        });

        Schema::table(AnimeRelations::TABLE_NAME, function (Blueprint $table) {
            $table->unique(['anime_id', 'related_anime_id', 'type']);
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('related_anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeRelations::TABLE_NAME);
    }
}
