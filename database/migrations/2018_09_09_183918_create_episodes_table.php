<?php

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Episode::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('season_id');
            $table->string('preview_image')->nullable();
            $table->unsignedInteger('number');
            $table->unsignedInteger('number_total');
            $table->unsignedMediumInteger('duration')->default(0);
            $table->dateTime('first_aired')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });

        Schema::table(Episode::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['season_id', 'number']);

            // Set foreign key constraints
            $table->foreign('season_id')->references('id')->on(Season::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Episode::TABLE_NAME);
    }
}
