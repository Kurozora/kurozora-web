<?php

use App\Models\MediaGameMode;
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
        Schema::create(MediaGameMode::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedTinyInteger('game_mode');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaGameMode::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'game_mode'], 'media_game_mode_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MediaGameMode::TABLE_NAME);
    }
};
