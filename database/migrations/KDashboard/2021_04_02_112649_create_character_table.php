<?php

namespace Database\Migrations\KDashboard;

use App\Models\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Character::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('name');
            $table->string('nickname');
            $table->string('japanese_name');
            $table->string('image_url');
            $table->bigInteger('favorite');
            $table->string('about');
            $table->timestamps();
        });

        Schema::table(Character::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Character::TABLE_NAME);
    }
}
