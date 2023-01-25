<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\People;
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
    public function up(): void
    {
        Schema::create(People::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('name');
            $table->string('given_name');
            $table->string('family_name');
            $table->string('alternative_name');
            $table->string('image_url');
            $table->bigInteger('birthday_year');
            $table->bigInteger('birthday_month');
            $table->bigInteger('birthday_day');
            $table->string('website');
            $table->bigInteger('favorite');
            $table->string('more');
            $table->timestamps();
        });

        Schema::table(People::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(People::TABLE_NAME);
    }
};
