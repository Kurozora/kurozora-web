<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Genre;
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
        Schema::create(Genre::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedInteger('unique_id')->autoIncrement()->unique();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('type');
            $table->string('genre');
            $table->timestamps();
        });

        Schema::table(Genre::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id', 'genre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Genre::TABLE_NAME);
    }
};
