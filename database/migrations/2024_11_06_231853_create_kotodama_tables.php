<?php

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Guess;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\Minigames\Kotodama\Word;
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
        Schema::create(Word::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->char('answer', Word::LENGTH)->unique();
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('hint_text')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_nsfw', 'released_at'], 'kotodama_words_schedule_index');
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create(DailyPuzzle::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_id')->unique();
            $table->date('puzzle_date')->unique();
            $table->unsignedInteger('puzzle_number')->unique();
            $table->timestamps();
        });

        Schema::table(DailyPuzzle::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('word_id')
                ->references('id')
                ->on(Word::TABLE_NAME)
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create(Game::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->char('guest_token', 64)->nullable();
            $table->unsignedBigInteger('word_id');
            $table->unsignedBigInteger('daily_puzzle_id')->nullable();
            $table->unsignedTinyInteger('mode');
            $table->char('versus_seed', 22)->nullable()->unique();
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('guess_count')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'finished_at']);
            $table->index(['daily_puzzle_id', 'status', 'guess_count', 'duration_ms'], 'kotodama_games_leaderboard_index');
            $table->index(['guest_token', 'word_id']);
        });

        Schema::table(Game::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('word_id')
                ->references('id')
                ->on(Word::TABLE_NAME)
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('daily_puzzle_id')
                ->references('id')
                ->on(DailyPuzzle::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create(Guess::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedTinyInteger('position');
            $table->char('guess', Word::LENGTH);
            $table->char('feedback', Word::LENGTH);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['game_id', 'position']);
        });

        Schema::table(Guess::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('game_id')
                ->references('id')
                ->on(Game::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create(UserStats::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('max_streak')->default(0);
            $table->date('last_daily_date')->nullable();
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->json('guess_distribution')->nullable();
            $table->unsignedBigInteger('total_duration_ms')->default(0);
            $table->timestamps();
        });

        Schema::table(UserStats::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
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
        Schema::dropIfExists(UserStats::TABLE_NAME);
        Schema::dropIfExists(Guess::TABLE_NAME);
        Schema::dropIfExists(Game::TABLE_NAME);
        Schema::dropIfExists(DailyPuzzle::TABLE_NAME);
        Schema::dropIfExists(Word::TABLE_NAME);
    }
};
