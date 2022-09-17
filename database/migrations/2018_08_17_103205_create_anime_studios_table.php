<?php

use App\Models\AnimeStudio;
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
        Schema::create(AnimeStudio::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('studio_id');
            $table->boolean('is_licensor');
            $table->boolean('is_producer');
            $table->boolean('is_studio');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(AnimeStudio::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['anime_id', 'studio_id']);

            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
            $table->foreign('studio_id')->references('id')->on('studios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(AnimeStudio::TABLE_NAME);
    }
};
