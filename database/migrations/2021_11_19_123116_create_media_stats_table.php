<?php

use App\Models\MediaStat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaStat::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->unsignedBigInteger('planning_count')->default(0);
            $table->unsignedBigInteger('watching_count')->default(0);
            $table->unsignedBigInteger('completed_count')->default(0);
            $table->unsignedBigInteger('on_hold_count')->default(0);
            $table->unsignedBigInteger('dropped_count')->default(0);
            $table->unsignedBigInteger('rating_1')->default(0);
            $table->unsignedBigInteger('rating_2')->default(0);
            $table->unsignedBigInteger('rating_3')->default(0);
            $table->unsignedBigInteger('rating_4')->default(0);
            $table->unsignedBigInteger('rating_5')->default(0);
            $table->unsignedBigInteger('rating_6')->default(0);
            $table->unsignedBigInteger('rating_7')->default(0);
            $table->unsignedBigInteger('rating_8')->default(0);
            $table->unsignedBigInteger('rating_9')->default(0);
            $table->unsignedBigInteger('rating_10')->default(0);
            $table->double('rating_average')->default(0.0);
            $table->unsignedBigInteger('rating_count')->default(0);
            $table->timestamps();
        });

        Schema::table(MediaStat::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaStat::TABLE_NAME);
    }
}
