<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\ProducerMagazine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProducerMagazineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ProducerMagazine::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('unique_id')->autoIncrement()->unique();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('type');
            $table->string('name');
            $table->timestamps();
        });

        Schema::table(ProducerMagazine::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(ProducerMagazine::TABLE_NAME);
    }
}
