<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\AnimeStaff;
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
        Schema::create(AnimeStaff::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('position_id');
            $table->timestamps();
        });

        Schema::table(AnimeStaff::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['anime_id', 'people_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(AnimeStaff::TABLE_NAME);
    }
};

