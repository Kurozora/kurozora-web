<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StatsService
{
    /**
     * Recompute a user's stats.
     *
     * @param User $user
     *
     * @return UserStats
     */
    public static function recompute(User $user): UserStats
    {
        $puzzleGames = Game::where('user_id', $user->id)
            ->whereIn('mode', [GameMode::Daily, GameMode::Archive])
            ->whereIn('status', [GameStatus::Won, GameStatus::Lost])
            ->with('dailyPuzzle')
            ->orderBy('finished_at')
            ->get();

        $distribution = [];
        $totalDurationMs = 0;
        $gamesWon = 0;

        foreach ($puzzleGames as $game) {
            $totalDurationMs += (int) ($game->duration_ms ?? 0);

            if ($game->status?->is(GameStatus::Won)) {
                $gamesWon++;
                $bucket = (string) $game->guess_count;
                $distribution[$bucket] = ($distribution[$bucket] ?? 0) + 1;
            }
        }

        // Streaks count consecutive dailies only.
        $dailyGames = $puzzleGames->filter(fn (Game $game) => $game->mode?->is(GameMode::Daily));

        [$currentStreak, $maxStreak, $lastDailyDate] = self::computeStreaks($dailyGames);

        $stats = UserStats::find($user->id) ?? new UserStats(['user_id' => $user->id]);
        $stats->user_id = $user->id;
        $stats->current_streak = $currentStreak;
        $stats->max_streak = $maxStreak;
        $stats->last_daily_date = $lastDailyDate;
        $stats->games_played = $puzzleGames->count();
        $stats->games_won = $gamesWon;
        $stats->guess_distribution = $distribution;
        $stats->total_duration_ms = $totalDurationMs;
        $stats->save();

        return $stats;
    }

    /**
     * The daily leaderboard for a puzzle.
     *
     * @param DailyPuzzle $puzzle
     * @param int               $limit
     *
     * @return Collection
     */
    public static function dailyLeaderboard(DailyPuzzle $puzzle, int $limit = 25): Collection
    {
        return Game::where('daily_puzzle_id', $puzzle->id)
            ->where('status', GameStatus::Won)
            ->whereNotNull('user_id')
            ->with('user')
            ->orderBy('guess_count')
            ->orderBy('duration_ms')
            ->limit($limit)
            ->get();
    }

    /**
     * The streak leaderboard.
     *
     * @param int $limit
     *
     * @return Collection
     */
    public static function streakLeaderboard(int $limit = 25): Collection
    {
        return UserStats::where('max_streak', '>', 0)
            ->with('user')
            ->orderByDesc('max_streak')
            ->orderByDesc('current_streak')
            ->limit($limit)
            ->get();
    }

    /**
     * Compute a user's streaks.
     *
     * @param Collection $dailyGames
     *
     * @return array{0: int, 1: int, 2: Carbon|null}
     */
    protected static function computeStreaks(Collection $dailyGames): array
    {
        $currentStreak = 0;
        $maxStreak = 0;
        $previousDate = null;
        $lastDailyDate = null;

        foreach ($dailyGames as $game) {
            $puzzleDate = $game->dailyPuzzle?->puzzle_date;

            if (!$puzzleDate) {
                continue;
            }

            $puzzleDate = Carbon::parse($puzzleDate);
            $lastDailyDate = $puzzleDate;

            if (!$game->status?->is(GameStatus::Won)) {
                $currentStreak = 0;
                $previousDate = $puzzleDate;
                continue;
            }

            if ($previousDate === null) {
                $currentStreak = 1;
            } else {
                $diffInDays = $previousDate->startOfDay()->diffInDays($puzzleDate->startOfDay());
                $currentStreak = $diffInDays === 1 ? $currentStreak + 1 : 1;
            }

            $maxStreak = max($maxStreak, $currentStreak);
            $previousDate = $puzzleDate;
        }

        // A missed day breaks the streak.
        if ($lastDailyDate && $lastDailyDate->startOfDay()->diffInDays(Carbon::now()->startOfDay()) > 1) {
            $currentStreak = 0;
        }

        return [$currentStreak, $maxStreak, $lastDailyDate];
    }
}
