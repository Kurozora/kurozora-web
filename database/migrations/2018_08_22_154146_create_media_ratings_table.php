<?php

use App\Models\MediaRating;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaRating::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('model');
            $table->unsignedBigInteger('user_id');
            $table->float('rating');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table(MediaRating::TABLE_NAME, function(Blueprint $table) {
            // Set index key constraints
            $table->index('user_id');

            // Set unique key constraints
            $table->unique(['user_id', 'model_id', 'model_type']);

            // Set foreign key constraints
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaRating::TABLE_NAME);
    }
}
