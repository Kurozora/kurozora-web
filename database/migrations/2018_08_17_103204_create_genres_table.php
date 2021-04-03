<?php

use App\Models\Genre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Genre::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->string('color')->default('#ffffff');
            $table->text('description')->nullable();
            $table->boolean('is_nsfw')->default(false);
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
        Schema::dropIfExists(Genre::TABLE_NAME);
    }
}
