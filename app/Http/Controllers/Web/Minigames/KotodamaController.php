<?php

namespace App\Http\Controllers\Web\Minigames;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\User;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\StatsService;
use App\View\Components\Kotodama\Countdown;
use App\View\Components\Kotodama\Summary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

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
     * The number of rows shown in the leaderboard peek.
     */
    const int PEEK_LIMIT = 3;

    /**
     * Show today's puzzle.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function daily(Request $request): Application|Factory|View
    {
        $state = $this->dailyState($request->user());

        return view('minigames.kotodama.daily', [
            'game' => $state->game,
            'puzzle' => $state->puzzle,
            'flash' => $state->flash,
            'mode' => GameMode::Daily(),
            'title' => __('Kotodama · Daily #:number', ['number' => $state->puzzle?->puzzle_number ?? 0]),
            'stats' => $state->stats,
            'winRate' => $state->winRate,
            'topEntries' => $state->topEntries,
            'nextPuzzleAt' => $state->nextPuzzleAt,
            'countdownUrl' => route('kotodama.section', 'countdown', false),
            'summaryUrl' => route('kotodama.section', 'summary', false),
        ]);
    }

    /**
     * Show a practice puzzle that can be replayed endlessly.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function unlimited(Request $request): Application|Factory|View
    {
        $game = GameCoordinator::startUnlimited(
            PuzzleResolver::unlimited(),
            $request->user(),
            GameCoordinator::guestTokenFor($request->session()->getId())
        );

        return view('minigames.kotodama.play', [
            'game' => $game,
            'flash' => null,
            'mode' => GameMode::Unlimited(),
            'title' => __('Kotodama · Unlimited'),
            'appArgument' => 'kotodama/unlimited',
            'canonicalUrl' => route('kotodama.unlimited'),
            'indexable' => true,
        ]);
    }

    /**
     * Show the puzzle behind a versus seed.
     *
     * @param Request $request
     * @param string  $seed
     *
     * @return Application|Factory|View
     */
    public function versus(Request $request, string $seed): Application|Factory|View
    {
        $challenger = Game::where('versus_seed', $seed)
            ->with(['word', 'user', 'guesses'])
            ->firstOrFail();

        $game = GameCoordinator::startUnlimited(
            $challenger->word,
            $request->user(),
            GameCoordinator::guestTokenFor($request->session()->getId())
        );
        $game->mode = GameMode::Versus();
        $game->save();

        return view('minigames.kotodama.play', [
            'game' => $game,
            'challenger' => $challenger,
            'flash' => null,
            'mode' => GameMode::Versus(),
            'title' => __('Kotodama · Versus'),
            'appArgument' => 'kotodama/versus/' . $seed,
            'canonicalUrl' => route('kotodama.versus', $seed),
            'indexable' => true,
        ]);
    }

    /**
     * Show a past puzzle.
     *
     * @param Request $request
     * @param string  $date
     *
     * @return Application|Factory|View
     */
    public function playArchive(Request $request, string $date): Application|Factory|View
    {
        $parsed = Carbon::parse($date);

        if (!$parsed->isPast() || $parsed->isToday()) {
            abort(404);
        }

        try {
            $puzzle = PuzzleResolver::archive($parsed);
        } catch (ModelNotFoundException) {
            $puzzle = null;
        }

        return view('minigames.kotodama.play', [
            'game' => $puzzle ? GameCoordinator::startArchive($puzzle, $request->user()) : null,
            'flash' => $puzzle ? null : __('No puzzle is available for that date.'),
            'mode' => GameMode::Archive(),
            'title' => __('Kotodama · :date', ['date' => $this->formattedDate($puzzle, $date)]),
            'appArgument' => 'kotodama/archive/' . $date,
            'canonicalUrl' => route('kotodama.archive.play', ['date' => $date]),
            'indexable' => false,
        ]);
    }

    /**
     * Render a section of the daily page.
     *
     * @param Request $request
     * @param string  $section
     *
     * @return Response
     */
    public function section(Request $request, string $section): Response
    {
        $state = $this->dailyState($request->user());

        return $this->render(match ($section) {
            'countdown' => new Countdown($state->game, $state->nextPuzzleAt, route('kotodama.section', 'countdown', false)),
            'summary' => new Summary($state->game, $state->stats, $state->winRate, $state->topEntries, route('kotodama.section', 'summary', false)),
            default => abort(404),
        });
    }

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
     * Today's puzzle, the player's game and the numbers shown beside them.
     *
     * @param User|null $user
     *
     * @return object
     */
    private function dailyState(?User $user): object
    {
        try {
            $puzzle = PuzzleResolver::today();
        } catch (ModelNotFoundException) {
            $puzzle = null;
        }

        $stats = $user ? UserStats::find($user->id) : null;
        $game = $puzzle && $user
            ? GameCoordinator::startDaily($puzzle, $user)->load(['word.subject', 'guesses'])
            : null;

        return (object) [
            'puzzle' => $puzzle,
            'game' => $game,
            'flash' => $puzzle ? null : __('No puzzle is available today.'),
            'stats' => $stats,
            'winRate' => $this->winRate($stats),
            'topEntries' => $puzzle ? StatsService::dailyLeaderboard($puzzle, self::PEEK_LIMIT) : collect(),
            'nextPuzzleAt' => $puzzle?->puzzle_date?->copy()->addDay()->startOfDay()->getTimestampMs(),
        ];
    }

    /**
     * The puzzle date formatted for display.
     *
     * @param DailyPuzzle|null $puzzle
     * @param string           $date
     *
     * @return string
     */
    private function formattedDate(?DailyPuzzle $puzzle, string $date): string
    {
        $puzzleDate = $puzzle?->puzzle_date ?? Carbon::parse($date);

        return $puzzleDate->locale(app()->getLocale())->isoFormat('ll');
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

    /**
     * Render the component as a page fragment.
     *
     * @param Component $component
     *
     * @return Response
     */
    protected function render(Component $component): Response
    {
        return response($component->shouldRender() ? Blade::renderComponent($component) : '');
    }
}
