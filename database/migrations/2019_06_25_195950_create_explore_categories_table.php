<?php

use App\Models\ExploreCategory;
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
    public function up()
    {
        Schema::create(ExploreCategory::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('slug');
            $table->string('type');
            $table->string('size');
            $table->smallInteger('position');
            $table->boolean('is_global');
            $table->timestamps();
        });

        Schema::table(ExploreCategory::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ExploreCategory::TABLE_NAME);
    }
};
