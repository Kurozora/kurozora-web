<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\AnimeProducer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeProducerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeProducer::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('producer_id');
            $table->boolean('is_licensor');
            $table->boolean('is_studio');
            $table->timestamps();
        });

        Schema::table(AnimeProducer::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['anime_id', 'producer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeProducer::TABLE_NAME);
    }
}
