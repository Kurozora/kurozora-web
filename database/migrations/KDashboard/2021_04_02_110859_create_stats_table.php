<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Stats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Stats::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('type');
            $table->bigInteger('current');
            $table->bigInteger('completed');
            $table->bigInteger('on_hold');
            $table->bigInteger('dropped');
            $table->bigInteger('planned');
            $table->bigInteger('score_1');
            $table->bigInteger('score_2');
            $table->bigInteger('score_3');
            $table->bigInteger('score_4');
            $table->bigInteger('score_5');
            $table->bigInteger('score_6');
            $table->bigInteger('score_7');
            $table->bigInteger('score_8');
            $table->bigInteger('score_9');
            $table->bigInteger('score_10');
            $table->timestamps();
        });

        Schema::table(Stats::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Stats::TABLE_NAME);
    }
}
