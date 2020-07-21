<?php

use App\Anime;
use App\AnimeImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeImages::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('anime_id');
            $table->integer('type');
            $table->string('url');
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->string('background_color')->nullable();
            $table->string('text_color_1')->nullable();
            $table->string('text_color_2')->nullable();
            $table->string('text_color_3')->nullable();
            $table->string('text_color_4')->nullable();
            $table->timestamps();
        });

        Schema::table(AnimeImages::TABLE_NAME, function (Blueprint $table) {
            // Set unique constraints
            $table->unique(['anime_id', 'type']);

            // Set foreign key constraints
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
        Schema::dropIfExists(AnimeImages::TABLE_NAME);
    }
}
