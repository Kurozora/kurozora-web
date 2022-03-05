<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserLibrary;
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
    public function up()
    {
        Schema::create(UserLibrary::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('anime_id');
            $table->tinyInteger('status');
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });

        Schema::table(UserLibrary::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['user_id', 'anime_id']);

            // Set unique key constraints
            $table->unique(['user_id', 'anime_id']);

            // Set foreign key constraints
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(UserLibrary::TABLE_NAME);
    }
};
