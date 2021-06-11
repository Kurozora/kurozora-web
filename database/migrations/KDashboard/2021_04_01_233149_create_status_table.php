<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Status;
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
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('type');
            $table->string('name');
            $table->timestamps();
        });

        Schema::table(Status::TABLE_NAME, function(Blueprint $table) {
            $table->primary(['id', 'type']);
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
