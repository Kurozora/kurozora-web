<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\PeopleManga;
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
        Schema::create(PeopleManga::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('manga_id');
            $table->unsignedBigInteger('position_id');
            $table->timestamps();
        });

        Schema::table(PeopleManga::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['people_id', 'manga_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(PeopleManga::TABLE_NAME);
    }
};
