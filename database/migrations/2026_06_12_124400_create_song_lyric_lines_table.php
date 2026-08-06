<?php

use App\Models\SongLyric;
use App\Models\SongLyricLine;
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
        Schema::create(SongLyricLine::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('song_lyric_id');
            $table->string('kind', 16)->default('original');
            $table->string('language', 16);
            $table->string('line_key', 16);
            $table->unsignedInteger('position');
            $table->integer('begin_ms')->nullable();
            $table->integer('end_ms')->nullable();
            $table->string('agent', 16)->nullable();
            $table->string('song_part', 32)->nullable();
            $table->text('text');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(SongLyricLine::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['song_lyric_id', 'kind', 'position']);
            $table->index(['song_lyric_id', 'line_key']);

            // Set foreign key constraints
            $table->foreign('song_lyric_id')
                ->references('id')
                ->on(SongLyric::TABLE_NAME)
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
        Schema::dropIfExists(SongLyricLine::TABLE_NAME);
    }
};
