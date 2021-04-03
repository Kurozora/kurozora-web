<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserLibrary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLibrariesTable extends Migration
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
            $table->tinyInteger('status')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table(UserLibrary::TABLE_NAME, function (Blueprint $table) {
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
}
