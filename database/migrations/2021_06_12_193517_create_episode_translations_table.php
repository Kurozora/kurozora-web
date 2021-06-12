<?php

use App\Models\Episode;
use App\Models\EpisodeTranslation;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpisodeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(EpisodeTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('episode_id');
            $table->string('locale', 2)->index();
            $table->string('title');
            $table->text('overview');
            $table->timestamps();
        });

        Schema::table(EpisodeTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['episode_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('episode_id')->references('id')->on(Episode::TABLE_NAME)->onDelete('cascade');
            $table->foreign('locale')->references('code')->on(Language::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(EpisodeTranslation::TABLE_NAME);
    }
}
