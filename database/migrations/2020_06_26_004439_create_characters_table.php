<?php

use App\Character;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Character::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('name');
            $table->mediumText('about')->nullable();
            $table->string('image')->nullable();
            $table->string('debut')->nullable();
            $table->string('status')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('favorite_food')->nullable();
            $table->string('height')->nullable();
            $table->bigInteger('age')->nullable();
            $table->unsignedDecimal('bust')->nullable();
            $table->unsignedDecimal('waist')->nullable();
            $table->unsignedDecimal('hip')->nullable();
            $table->unsignedTinyInteger('birth_day')->nullable();
            $table->unsignedTinyInteger('birth_month')->nullable();
            $table->unsignedTinyInteger('astrological_sign')->nullable();
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
