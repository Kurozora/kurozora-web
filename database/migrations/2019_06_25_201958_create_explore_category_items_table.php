<?php

use App\Models\ExploreCategory;
use App\Models\ExploreCategoryItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(ExploreCategoryItem::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('explore_category_id');
            $table->morphs('model');
            $table->integer('position');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(ExploreCategoryItem::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('position');
            $table->index('deleted_at');

            // Set foreign key constraints
            $table->foreign('explore_category_id')
                ->references('id')
                ->on(ExploreCategory::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(ExploreCategoryItem::TABLE_NAME);
    }
};
