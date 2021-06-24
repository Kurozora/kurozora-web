<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Status::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('name');
            $table->string('description');
            $table->string('color');
            $table->timestamps();
        });

        Schema::table(Status::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(Status::TABLE_NAME);
    }
}
