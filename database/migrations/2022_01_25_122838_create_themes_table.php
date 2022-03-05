<?php

use App\Models\Theme;
use App\Models\TvRating;
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
        Schema::create(Theme::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tv_rating_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('color')->default('#ffffff');
            $table->text('description')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->timestamps();
        });

        Schema::table(Theme::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['slug']);

            // Set foreign key constraints
            $table->foreign('tv_rating_id')->references('id')->on(TvRating::TABLE_NAME)->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Theme::TABLE_NAME);
    }
};
