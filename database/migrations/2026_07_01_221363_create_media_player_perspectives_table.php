<?php

use App\Models\MediaPlayerPerspective;
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
        Schema::create(MediaPlayerPerspective::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedTinyInteger('player_perspective');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaPlayerPerspective::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'player_perspective'], 'media_player_perspective_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MediaPlayerPerspective::TABLE_NAME);
    }
};
