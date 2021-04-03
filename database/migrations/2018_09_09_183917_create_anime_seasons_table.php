<?php

use App\Models\Anime;
use App\Models\AnimeSeason;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeSeasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeSeason::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anime_id');
            $table->integer('number');
            $table->string('title')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table(AnimeSeason::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(AnimeSeason::TABLE_NAME);
    }
}
