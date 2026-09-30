<?php

namespace App\Http\Controllers\Web\Minigames;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\User;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class KotodamaController extends Controller
{
    /**
     * The list of valid leaderboard tabs.
     */
    private const array LEADERBOARD_TABS = ['daily', 'streak'];

    /**
     * The number of rows shown on a leaderboard.
     */
    const int LEADERBOARD_LIMIT = 25;

    /**
     * The number of past puzzles shown in the archive.
     */
    const int ARCHIVE_LIMIT = 365;

    /**
     * The shortest visible bar of the guess distribution.
     */
    const int MINIMUM_BAR_PERCENT = 4;

    /**
     * Show the daily and streak leaderboards.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function leaderboards(Request $request): Application|Factory|View
    {
        $tab = $request->string('tab')->value();

        if (!in_array($tab, self::LEADERBOARD_TABS, true)) {
            $tab = 'daily';
        }

        return view('minigames.kotodama.leaderboards', [
            'tab' => $tab,
            'dailyEntries' => $tab === 'daily' ? $this->dailyEntries() : collect(),
            'streakEntries' => $tab === 'streak' ? $this->streakEntries() : collect(),
        ]);
    }

    /**
     * Show the signed-in player's stats.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function stats(Request $request): Application|Factory|View
    {
        $stats = $this->userStats($request->user());

        return view('minigames.kotodama.stats', [
            'stats' => $stats,
            'winRate' => $this->winRate($stats),
            'distribution' => $this->distribution($stats),
            'averageGuesses' => $stats?->getAverageGuesses(),
        ]);
    }

    /**
     * Show the past puzzles available to play.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function archive(Request $request): Application|Factory|View
    {
        return view('minigames.kotodama.archive', [
            'entries' => $this->archiveEntries($request->user()),
        ]);
    }

    /**
     * Rows for the daily leaderboard tab.
     *
     * @return Collection
     */
    private function dailyEntries(): Collection
    {
        try {
            $puzzle = PuzzleResolver::today();
        } catch (ModelNotFoundException) {
            return collect();
        }

        return StatsService::dailyLeaderboard($puzzle, self::LEADERBOARD_LIMIT);
    }

    /**
     * Rows for the streak leaderboard tab.
     *
     * @return Collection
     */
    private function streakEntries(): Collection
    {
        return StatsService::streakLeaderboard(self::LEADERBOARD_LIMIT);
    }

    /**
     * The stats row for the given player.
     *
     * @param User|null $user
     *
     * @return UserStats|null
     */
    private function userStats(?User $user): ?UserStats
    {
        if (!$user) {
            return null;
        }

        return UserStats::find($user->id)
            ?? StatsService::recompute($user);
    }

    /**
     * The share of games the player has won.
     *
     * @param UserStats|null $stats
     *
     * @return int
     */
    private function winRate(?UserStats $stats): int
    {
        return (int) round(($stats?->getWinRate() ?? 0) * 100);
    }

    /**
     * One bar per guess count.
     *
     * @param UserStats|null $stats
     *
     * @return Collection
     */
    private function distribution(?UserStats $stats): Collection
    {
        $counts = $stats?->guess_distribution ?? [];
        $busiest = max(1, max($counts ?: [0]));

        return collect(range(1, Game::MAX_GUESSES))
            ->map(function (int $bucket) use ($counts, $busiest) {
                $count = (int) ($counts[(string) $bucket] ?? 0);
                $percent = (int) round($count / $busiest * 100);

                return (object) [
                    'bucket' => $bucket,
                    'count' => $count,
                    'percent' => $count > 0 ? max($percent, self::MINIMUM_BAR_PERCENT) : 0,
                ];
            });
    }

    /**
     * The past puzzles available to the given player.
     *
     * @param User|null $user
     *
     * @return Collection
     */
    private function archiveEntries(?User $user): Collection
    {
        $puzzles = DailyPuzzle::where('puzzle_date', '<', Carbon::now()->toDateString())
            ->orderByDesc('puzzle_date')
            ->limit(self::ARCHIVE_LIMIT)
            ->get();

        $finishedGames = Game::whereIn('daily_puzzle_id', $puzzles->pluck('id'))
            ->whereIn('mode', [GameMode::Daily, GameMode::Archive])
            ->whereIn('status', [GameStatus::Won, GameStatus::Lost])
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->get(['daily_puzzle_id', 'status']);

        $solvedPuzzleIDs = $finishedGames->filter(fn (Game $game) => $game->status?->is(GameStatus::Won))
            ->pluck('daily_puzzle_id');
        $finishedPuzzleIDs = $finishedGames->pluck('daily_puzzle_id');

        return $puzzles->map(function (DailyPuzzle $puzzle) use ($solvedPuzzleIDs, $finishedPuzzleIDs) {
            return (object) [
                'date' => $puzzle->puzzle_date?->toDateString(),
                'formattedDate' => $puzzle->puzzle_date?->locale(app()->getLocale())->isoFormat('ll'),
                'puzzleNumber' => $puzzle->puzzle_number,
                'solved' => $solvedPuzzleIDs->contains($puzzle->id),
                'finished' => $finishedPuzzleIDs->contains($puzzle->id),
            ];
        });
    }
}
