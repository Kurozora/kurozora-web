<?php

use App\Models\ExploreCategory;
use App\Models\ExploreCategoryItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExploreCategoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ExploreCategoryItem::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('explore_category_id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->timestamps();
        });

        Schema::table(ExploreCategoryItem::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('explore_category_id')->references('id')->on(ExploreCategory::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ExploreCategoryItem::TABLE_NAME);
    }
}