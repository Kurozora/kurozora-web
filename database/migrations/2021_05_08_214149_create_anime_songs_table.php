<?php

use App\Models\Anime;
use App\Models\AnimeSong;
use App\Models\Song;
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
        Schema::create(AnimeSong::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('song_id');
            $table->tinyInteger('type');
            $table->integer('position')->nullable();
            $table->string('episodes')->nullable();
            $table->timestamps();
        });

        Schema::table(AnimeSong::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['anime_id', 'song_id', 'type']);

            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('song_id')->references('id')->on(Song::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(AnimeSong::TABLE_NAME);
    }
};
