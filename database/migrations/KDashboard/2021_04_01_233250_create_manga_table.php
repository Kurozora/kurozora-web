<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Manga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Manga::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('title');
            $table->string('title_english');
            $table->string('title_japanese');
            $table->string('title_synonym');
            $table->string('image_url');
            $table->string('synopsis');
            $table->double('score');
            $table->bigInteger('voter');
            $table->bigInteger('rank');
            $table->bigInteger('popularity');
            $table->bigInteger('member');
            $table->bigInteger('favorite');
            $table->bigInteger('manga_type_id');
            $table->bigInteger('volume');
            $table->bigInteger('chapter');
            $table->bigInteger('manga_status_id');
            $table->bigInteger('start_year');
            $table->bigInteger('start_month');
            $table->bigInteger('start_day');
            $table->bigInteger('end_year');
            $table->bigInteger('end_month');
            $table->bigInteger('end_day');
            $table->timestamps();
        });

        Schema::table(Manga::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Manga::TABLE_NAME);
    }
}
