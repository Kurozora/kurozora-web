<?php

use App\Models\Genre;
use App\Models\MediaGenre;
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
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('genre_id');
            $table->timestamps();
        });

        Schema::table(MediaGenre::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['model_type', 'model_id', 'genre_id']);

            // Set foreign key constraints
            $table->foreign('genre_id')->references('id')->on(Genre::TABLE_NAME)->onDelete('cascade');
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
