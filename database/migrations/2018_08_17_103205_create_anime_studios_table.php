<?php

use App\Models\AnimeStudio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeStudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeStudio::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('studio_id');
            $table->boolean('is_licensor');
            $table->boolean('is_producer');
            $table->boolean('is_studio');
            $table->timestamps();
        });

        Schema::table(AnimeStudio::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['anime_id', 'studio_id']);

            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
            $table->foreign('studio_id')->references('id')->on('studios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeStudio::TABLE_NAME);
    }
}
