<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Song;
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
        Schema::create(Song::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('unique_id')->autoIncrement()->unique();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('type');
            $table->string('song');
            $table->timestamps();
        });

        Schema::table(Song::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id', 'anime_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Song::TABLE_NAME);
    }
};
