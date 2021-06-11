<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\StatsHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(StatsHistory::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('media_id');
            $table->string('type');
            $table->double('score');
            $table->bigInteger('voter');
            $table->bigInteger('rank');
            $table->bigInteger('popularity');
            $table->bigInteger('member');
            $table->bigInteger('favorite');
            $table->timestamps();
        });

        Schema::table(StatsHistory::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id', 'media_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(StatsHistory::TABLE_NAME);
    }
}
