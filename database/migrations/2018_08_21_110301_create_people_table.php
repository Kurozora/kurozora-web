<?php

use App\Models\Person;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Person::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('given_name')->nullable();
            $table->json('alternative_names')->nullable();
            $table->text('about')->nullable();
            $table->date('birth_date')->nullable();
            $table->json('website_urls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Person::TABLE_NAME);
    }
}
