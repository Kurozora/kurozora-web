<?php

use App\Models\SongLyricLine;
use App\Models\SongLyricWord;
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
        Schema::create(SongLyricWord::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('song_lyric_line_id');
            $table->unsignedInteger('position');
            $table->integer('begin_ms');
            $table->integer('end_ms');
            $table->text('text');
            $table->boolean('is_background')->default(false);
            $table->boolean('trailing_space')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(SongLyricWord::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['song_lyric_line_id', 'position']);

            // Set foreign key constraints
            $table->foreign('song_lyric_line_id')
                ->references('id')
                ->on(SongLyricLine::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(SongLyricWord::TABLE_NAME);
    }
};
