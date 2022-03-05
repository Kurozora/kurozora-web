<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\EmptyID;
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
        Schema::create(EmptyID::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('unique_id')->autoIncrement()->unique();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('type');
            $table->timestamps();
        });

        Schema::table(EmptyID::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(EmptyID::TABLE_NAME);
    }
};
