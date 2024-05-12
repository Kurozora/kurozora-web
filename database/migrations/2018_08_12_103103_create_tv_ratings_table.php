<?php

use App\Models\TvRating;
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
        Schema::create(TvRating::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->unsignedTinyInteger('weight');
            $table->timestamps();
        });

        Schema::table(TvRating::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(TvRating::TABLE_NAME);
    }
};
