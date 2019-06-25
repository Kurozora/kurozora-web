<?php

use App\ExplorePageCategory;
use App\ExplorePageCategoryGenre;
use App\Genre;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExplorePageCategoryGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ExplorePageCategoryGenre::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('explore_page_category_id')->unsigned();
            $table->foreign('explore_page_category_id')->references('id')->on(ExplorePageCategory::TABLE_NAME)->onDelete('cascade');

            $table->integer('genre_id')->unsigned();
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
        Schema::dropIfExists(ExplorePageCategoryGenre::TABLE_NAME);
    }
}
