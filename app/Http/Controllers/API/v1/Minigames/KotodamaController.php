<?php

namespace App\Http\Controllers\API\v1\Minigames;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Minigames\Kotodama\CreateVersusRequest;
use App\Http\Requests\Minigames\Kotodama\GetArchiveIndexRequest;
use App\Http\Requests\Minigames\Kotodama\GetDailyLeaderboardRequest;
use App\Http\Requests\Minigames\Kotodama\GetDailyPuzzleRequest;
use App\Http\Requests\Minigames\Kotodama\SubmitGuessRequest;
use App\Http\Resources\Minigames\Kotodama\ArchiveEntryResource;
use App\Http\Resources\Minigames\Kotodama\GameResource;
use App\Http\Resources\Minigames\Kotodama\LeaderboardEntryResource;
use App\Http\Resources\Minigames\Kotodama\ShareGridResource;
use App\Http\Resources\Minigames\Kotodama\StreakEntryResource;
use App\Http\Resources\Minigames\Kotodama\UserStatsResource;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\UserStats;
use App\Models\Minigames\Kotodama\Word;
use App\Services\Minigames\Kotodama\GameCoordinator;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use App\Services\Minigames\Kotodama\StatsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class KotodamaController extends Controller
{
    /**
     * Return today's daily puzzle.
     *
     * @param GetDailyPuzzleRequest $request
     *
     * @return JsonResponse
     */
    public function daily(GetDailyPuzzleRequest $request): JsonResponse
    {
        $puzzle = PuzzleResolver::today();
        $game = GameCoordinator::startDaily($puzzle, $request->user());

        return JSONResult::success([
            'data' => [GameResource::make($game)],
        ]);
    }

    /**
     * Start an unlimited game.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unlimited(Request $request): JsonResponse
    {
        $word = $this->pickUnlimitedWord();
        $game = GameCoordinator::startUnlimited(
            $word,
            $request->user(),
            $this->guestTokenForRequest($request)
        );

        return JSONResult::success([
            'data' => [GameResource::make($game)],
        ]);
    }

    /**
     * Return the past puzzles available to replay.
     *
     * @param GetArchiveIndexRequest $request
     *
     * @return JsonResponse
     */
    public function archiveIndex(GetArchiveIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $puzzles = DailyPuzzle::where('puzzle_date', '<', Carbon::now()->toDateString())
            ->orderByDesc('puzzle_date')
            ->cursorPaginate($data['limit'] ?? 25);

        $finishedGames = Game::whereIn('daily_puzzle_id', $puzzles->pluck('id'))
            ->where('user_id', $request->user()->id)
            ->whereIn('mode', [GameMode::Daily, GameMode::Archive])
            ->whereIn('status', [GameStatus::Won, GameStatus::Lost])
            ->get(['daily_puzzle_id', 'status']);

        $solvedPuzzleIDs = $finishedGames->filter(fn (Game $game) => $game->status?->is(GameStatus::Won))
            ->pluck('daily_puzzle_id');
        $finishedPuzzleIDs = $finishedGames->pluck('daily_puzzle_id');

        $nextPageURL = str_replace($request->root(), '', $puzzles->nextPageUrl() ?? '');

        $entries = $puzzles->getCollection()
            ->map(fn (DailyPuzzle $puzzle) => ArchiveEntryResource::make(
                $puzzle,
                $solvedPuzzleIDs->contains($puzzle->id),
                $finishedPuzzleIDs->contains($puzzle->id)
            ));

        return JSONResult::success([
            'data' => $entries,
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Resolve an archive puzzle.
     *
     * @param Request $request
     * @param string  $date
     *
     * @return JsonResponse
     */
    public function archive(Request $request, string $date): JsonResponse
    {
        $parsedDate = Carbon::parse($date);

        if (!$parsedDate->isPast() || $parsedDate->isToday()) {
            throw (new ModelNotFoundException)->setModel(DailyPuzzle::class);
        }

        $puzzle = PuzzleResolver::archive($parsedDate);
        $game = GameCoordinator::startArchive($puzzle, $request->user());

        return JSONResult::success([
            'data' => [GameResource::make($game)],
        ]);
    }

    /**
     * Create a new versus game with a shareable seed.
     *
     * @param CreateVersusRequest $request
     *
     * @return JsonResponse
     */
    public function createVersus(CreateVersusRequest $request): JsonResponse
    {
        $data = $request->validated();

        $word = !empty($data['wordID'])
            ? Word::findOrFail($data['wordID'])
            : PuzzleResolver::pickNextWord();

        $game = GameCoordinator::startVersus($word, $request->user());

        return JSONResult::success([
            'data' => [GameResource::make($game)],
        ]);
    }

    /**
     * Join a versus game by seed.
     *
     * @param Request $request
     * @param string  $seed
     *
     * @return JsonResponse
     */
    public function joinVersus(Request $request, string $seed): JsonResponse
    {
        $originalGame = Game::where('versus_seed', $seed)
            ->firstOrFail();

        $user = $request->user();
        $guestToken = $this->guestTokenForRequest($request);

        $existing = Game::where('mode', GameMode::Versus)
            ->where('word_id', $originalGame->word_id)
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->when(!$user && $guestToken, fn ($query) => $query->whereNull('user_id')->where('guest_token', $guestToken))
            ->where('id', '!=', $originalGame->id)
            ->first();

        if ($existing) {
            $game = $existing;
        } else {
            $game = GameCoordinator::startUnlimited($originalGame->word, $user, $guestToken);
            $game->mode = GameMode::Versus();
            $game->versus_seed = null;
            $game->save();
        }

        return JSONResult::success([
            'data' => [GameResource::make($game, $originalGame)],
        ]);
    }

    /**
     * Return a single game.
     *
     * @param Request $request
     * @param Game    $game
     *
     * @return JsonResponse
     */
    public function details(Request $request, Game $game): JsonResponse
    {
        $this->ensureOwner($request, $game);

        return JSONResult::success([
            'data' => [GameResource::make($game)],
        ]);
    }

    /**
     * Submit a guess against a game.
     *
     * @param SubmitGuessRequest $request
     * @param Game         $game
     *
     * @return JsonResponse
     */
    public function guess(SubmitGuessRequest $request, Game $game): JsonResponse
    {
        $this->ensureOwner($request, $game);

        $data = $request->validated();
        GameCoordinator::submitGuess($game, $data['guess']);

        return JSONResult::success([
            'data' => [GameResource::make($game->refresh())],
        ]);
    }

    /**
     * Abandon an in-progress game.
     *
     * @param Request    $request
     * @param Game $game
     *
     * @return JsonResponse
     */
    public function abandon(Request $request, Game $game): JsonResponse
    {
        $this->ensureOwner($request, $game);

        GameCoordinator::abandon($game);

        return JSONResult::success([
            'data' => [GameResource::make($game->refresh())],
        ]);
    }

    /**
     * Return the emoji share grid for a finished game.
     *
     * @param Request    $request
     * @param Game $game
     *
     * @return JsonResponse
     */
    public function share(Request $request, Game $game): JsonResponse
    {
        if (!$game->shouldRevealAnswer()) {
            throw new ConflictHttpException(__('This game hasn\'t finished yet.'));
        }

        return JSONResult::success([
            'data' => [ShareGridResource::make($game)],
        ]);
    }

    /**
     * Return the daily leaderboard for a date (defaults to today).
     *
     * @param GetDailyLeaderboardRequest $request
     * @param string|null                $date
     *
     * @return JsonResponse
     */
    public function dailyLeaderboard(GetDailyLeaderboardRequest $request, ?string $date = null): JsonResponse
    {
        $resolveDate = $date ? Carbon::parse($date) : Carbon::now();

        if ($resolveDate->isFuture()) {
            throw (new ModelNotFoundException)->setModel(DailyPuzzle::class);
        }

        $puzzle = PuzzleResolver::archive($resolveDate);

        $data = $request->validated();
        $limit = (int) ($data['limit'] ?? 25);

        $games = StatsService::dailyLeaderboard($puzzle, $limit);
        $entries = $games->values()
            ->map(fn (Game $game, int $index) => LeaderboardEntryResource::make($game, $index + 1));

        return JSONResult::success([
            'data' => $entries,
        ]);
    }

    /**
     * Return the all-time streak leaderboard.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function streakLeaderboard(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 25);
        $limit = max(1, min($limit, 100));

        $stats = StatsService::streakLeaderboard($limit);

        $entries = $stats->values()
            ->map(fn (UserStats $row, int $index) => StreakEntryResource::make($row, $index + 1));

        return JSONResult::success([
            'data' => $entries,
        ]);
    }

    /**
     * Return the authenticated user's Kotodama stats.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = UserStats::find($user->id)
            ?? StatsService::recompute($user);

        return JSONResult::success([
            'data' => [UserStatsResource::make($stats)],
        ]);
    }

    /**
     * Ensure the caller owns the given game.
     *
     * @param Request    $request
     * @param Game $game
     *
     * @return void
     */
    protected function ensureOwner(Request $request, Game $game): void
    {
        $user = $request->user();

        if ($user && $game->user_id === $user->id) {
            return;
        }

        $guestToken = $this->guestTokenForRequest($request);

        if (!$user && $guestToken && $game->guest_token === $guestToken) {
            return;
        }

        throw (new ModelNotFoundException)->setModel(Game::class);
    }

    /**
     * The guest token for a request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function guestTokenForRequest(Request $request): ?string
    {
        $sessionId = null;

        if ($request->hasSession()) {
            try {
                $sessionId = $request->session()->getId();
            } catch (\Throwable) {
                $sessionId = null;
            }
        }

        if (!$sessionId) {
            $sessionId = $request->header('X-Kurozora-Guest-Token');
        }

        return GameCoordinator::guestTokenFor($sessionId);
    }

    /**
     * The word for a new unlimited game.
     *
     * @return Word
     */
    protected function pickUnlimitedWord(): Word
    {
        $word = Word::query()
            ->eligibleForSchedule()
            ->safeToReveal()
            ->with(['subject'])
            ->randomFirst();

        if (!$word) {
            throw (new ModelNotFoundException)->setModel(Word::class);
        }

        return $word;
    }
}
