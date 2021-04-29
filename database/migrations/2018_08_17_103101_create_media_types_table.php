<?php

use App\Models\MediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaType::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        Schema::table(MediaType::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaType::TABLE_NAME);
    }
}
