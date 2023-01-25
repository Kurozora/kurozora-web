<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\MediaGenre;
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
        Schema::create(MediaGenre::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('type');
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('genre_id');
            $table->timestamps();
        });

        Schema::table(MediaGenre::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['type', 'media_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MediaGenre::TABLE_NAME);
    }
};
