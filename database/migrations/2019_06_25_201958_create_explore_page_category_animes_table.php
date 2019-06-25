<?php

use App\Anime;
use App\ExplorePageCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExplorePageCategoryAnimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('explore_page_category_animes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('explore_page_category_id')->unsigned();
            $table->foreign('explore_page_category_id')->references('id')->on(ExplorePageCategory::TABLE_NAME)->onDelete('cascade');

            $table->integer('anime_id')->unsigned();
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
        Schema::dropIfExists('explore_page_category_animes');
    }
}
