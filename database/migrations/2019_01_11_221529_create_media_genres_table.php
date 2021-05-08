<?php

use App\Models\Anime;
use App\Models\MediaGenre;
use App\Models\Genre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaGenre::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('genre_id');
            $table->string('type');
            $table->timestamps();
        });

        Schema::table(MediaGenre::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['media_id', 'genre_id', 'type']);

            // Set foreign key constraints
            $table->foreign('genre_id')->references('id')->on(Genre::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaGenre::TABLE_NAME);
    }
}
