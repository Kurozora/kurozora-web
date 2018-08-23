<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimeRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anime_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('anime_id')->unsigned()->unique();
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');

            $table->integer('user_id')->unsigned()->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->float('rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anime_ratings');
    }
}
