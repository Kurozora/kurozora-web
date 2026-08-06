<?php

use App\Models\Song;
use App\Models\SongLyric;
use App\Models\User;
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
        Schema::create(SongLyric::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('song_id');
            $table->string('source', 32)->default('apple');
            $table->string('language', 16);
            $table->string('timing', 8)->default('word');
            $table->integer('leading_silence_ms')->nullable();
            $table->integer('lyric_offset_ms')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('agents')->nullable();
            $table->string('status', 16)->default('pending');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(SongLyric::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['song_id', 'status', 'deleted_at']);

            // Set unique key constraints
            $table->unique(['song_id', 'source', 'language']);

            // Set foreign key constraints
            $table->foreign('song_id')
                ->references('id')
                ->on(Song::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('submitted_by')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->nullOnDelete()
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
        Schema::dropIfExists(SongLyric::TABLE_NAME);
    }
};
