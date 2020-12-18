<?php

use App\Models\Anime;
use App\Models\ExplorePageCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExplorePageCategoryAnimesTable extends Migration
{
    const TABLE_NAME = 'explore_page_category_animes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('explore_page_category_id');
            $table->unsignedBigInteger('anime_id');
            $table->timestamps();
        });

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('explore_page_category_id')->references('id')->on(ExplorePageCategory::TABLE_NAME)->onDelete('cascade');
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
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
