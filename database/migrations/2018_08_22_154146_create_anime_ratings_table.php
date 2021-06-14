<?php

use App\Models\Anime;
use App\Models\AnimeRating;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeRating::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('user_id');
            $table->float('rating');
            $table->timestamps();
        });

        Schema::table(AnimeRating::TABLE_NAME, function(Blueprint $table) {
            // Set unique key constraints
            $table->unique(['anime_id', 'user_id']);

            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeRating::TABLE_NAME);
    }
}
