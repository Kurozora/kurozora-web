<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Anime;
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
        Schema::create(Anime::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('title');
            $table->string('title_english');
            $table->string('title_japanese');
            $table->string('title_synonym');
            $table->string('image_url');
            $table->string('video_url');
            $table->string('synopsis');
            $table->double('score');
            $table->bigInteger('voter');
            $table->bigInteger('rank');
            $table->bigInteger('popularity');
            $table->bigInteger('member');
            $table->bigInteger('favorite');
            $table->bigInteger('anime_type_id');
            $table->bigInteger('episode');
            $table->bigInteger('anime_status_id');
            $table->bigInteger('start_year');
            $table->bigInteger('start_month');
            $table->bigInteger('start_day');
            $table->bigInteger('end_year');
            $table->bigInteger('end_month');
            $table->bigInteger('end_day');
            $table->string('airing_day');
            $table->string('airing_time');
            $table->string('premiered');
            $table->bigInteger('anime_source_id');
            $table->bigInteger('duration');
            $table->bigInteger('anime_rating_id');
            $table->timestamps();
        });

        Schema::table(Anime::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Anime::TABLE_NAME);
    }
};
