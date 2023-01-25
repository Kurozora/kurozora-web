<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\MangaMagazine;
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
        Schema::create(MangaMagazine::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('manga_id');
            $table->unsignedBigInteger('magazine_id');
            $table->timestamps();
        });

        Schema::table(MangaMagazine::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['manga_id', 'magazine_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MangaMagazine::TABLE_NAME);
    }
};
