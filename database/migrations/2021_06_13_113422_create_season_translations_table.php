<?php

use App\Models\Language;
use App\Models\Season;
use App\Models\SeasonTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(SeasonTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('season_id');
            $table->string('locale', 2)->index();
            $table->string('title');
            $table->text('synopsis');
            $table->timestamps();
        });

        Schema::table(SeasonTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['season_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('season_id')->references('id')->on(Season::TABLE_NAME)->onDelete('cascade');
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
        Schema::dropIfExists(SeasonTranslation::TABLE_NAME);
    }
}
