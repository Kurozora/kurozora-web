<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
use App\Models\Minigames\Kotodama\Guess;
use App\Models\Minigames\Kotodama\Word;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class GameCoordinator
{
    /**
     * Start a daily game for the given puzzle.
     *
     * @param DailyPuzzle $puzzle
     * @param User|null         $user
     * @param string|null       $guestToken
     *
     * @return Game
     */
    public static function startDaily(DailyPuzzle $puzzle, ?User $user, ?string $guestToken): Game
    {
        return self::findOrCreateGame(
            $user,
            $guestToken,
            $puzzle->word_id,
            $puzzle->id,
            GameMode::Daily()
        );
    }

    /**
     * Start an unlimited practice game.
     *
     * @param Word  $word
     * @param User|null   $user
     * @param string|null $guestToken
     *
     * @return Game
     */
    public static function startUnlimited(Word $word, ?User $user, ?string $guestToken): Game
    {
        return self::createGame(
            $user,
            $guestToken,
            $word->id,
            null,
            GameMode::Unlimited()
        );
    }

    /**
     * Start a versus game with a shareable seed.
     *
     * @param Word $word
     * @param User       $user
     *
     * @return Game
     */
    public static function startVersus(Word $word, User $user): Game
    {
        $game = new Game();
        $game->user_id = $user->id;
        $game->word_id = $word->id;
        $game->mode = GameMode::Versus();
        $game->status = GameStatus::InProgress();
        $game->versus_seed = self::generateVersusSeed();
        $game->started_at = Carbon::now();
        $game->save();

        return $game;
    }

    /**
     * Start an archive game.
     *
     * @param DailyPuzzle $puzzle
     * @param User              $user
     *
     * @return Game
     */
    public static function startArchive(DailyPuzzle $puzzle, User $user): Game
    {
        return self::findOrCreateGame(
            $user,
            null,
            $puzzle->word_id,
            $puzzle->id,
            GameMode::Archive()
        );
    }

    /**
     * Submit a guess against an in-progress game.
     *
     * @param Game $game
     * @param string     $guess
     *
     * @return Guess
     */
    public static function submitGuess(Game $game, string $guess): Guess
    {
        if ($game->isFinished()) {
            throw new ConflictHttpException(__('This game has already finished.'));
        }

        $guess = strtolower($guess);
        $game->loadMissing('word');
        $answer = $game->word->answer;

        self::validateGuess($guess, $answer, $game);

        return DB::transaction(function () use ($game, $guess, $answer) {
            $position = $game->guess_count + 1;
            $feedback = GuessEvaluator::evaluate($guess, $answer);

            $record = new Guess();
            $record->game_id = $game->id;
            $record->position = $position;
            $record->guess = $guess;
            $record->feedback = $feedback;
            $record->save();

            $game->guess_count = $position;

            if ($guess === $answer) {
                $game->status = GameStatus::Won();
                self::finalize($game);
            } else if ($position >= Game::MAX_GUESSES) {
                $game->status = GameStatus::Lost();
                self::finalize($game);
            } else {
                $game->save();
            }

            return $record;
        });
    }

    /**
     * Abandon an in-progress game.
     *
     * @param Game $game
     *
     * @return Game
     */
    public static function abandon(Game $game): Game
    {
        if ($game->isFinished()) {
            return $game;
        }

        $game->status = GameStatus::Abandoned();
        self::finalize($game);

        return $game;
    }

    /**
     * Finalize a finished game.
     *
     * @param Game $game
     *
     * @return void
     */
    public static function finalize(Game $game): void
    {
        if (!$game->finished_at) {
            $game->finished_at = Carbon::now();
        }

        if ($game->duration_ms === null && $game->started_at) {
            $startedAtMs = $game->started_at->getTimestampMs();
            $finishedAtMs = $game->finished_at->getTimestampMs();
            $game->duration_ms = max(0, $finishedAtMs - $startedAtMs);
        }

        $game->save();

        if ($game->user_id) {
            $user = User::find($game->user_id);

            if ($user) {
                StatsService::recompute($user);
            }
        }
    }

    /**
     * Claim any guest games for the given user.
     *
     * @param User   $user
     * @param string $guestToken
     *
     * @return int The number of claimed games.
     */
    public static function claimForUser(User $user, string $guestToken): int
    {
        if ($guestToken === '') {
            return 0;
        }

        $claimed = Game::whereNull('user_id')
            ->where('guest_token', $guestToken)
            ->update([
                'user_id' => $user->id,
                'guest_token' => null,
            ]);

        if ($claimed > 0) {
            StatsService::recompute($user);
        }

        return $claimed;
    }

    /**
     * Returns the guest token for a session identifier.
     *
     * @param string|null $sessionID
     *
     * @return string|null
     */
    public static function guestTokenFor(?string $sessionID): ?string
    {
        if ($sessionID === null || $sessionID === '') {
            return null;
        }

        return hash('sha256', $sessionID);
    }

    /**
     * Validate a guess.
     *
     * @param string     $guess
     * @param string     $answer
     * @param Game $game
     *
     * @return void
     */
    protected static function validateGuess(string $guess, string $answer, Game $game): void
    {
        $expectedLength = mb_strlen($answer);

        if (mb_strlen($guess) !== $expectedLength) {
            throw ValidationException::withMessages([
                'guess' => [__('The guess must be exactly :count letters long.', ['count' => $expectedLength])],
            ]);
        }

        if (!preg_match('/^[a-z]+$/', $guess)) {
            throw ValidationException::withMessages([
                'guess' => [__('The guess may only contain lowercase letters a–z.')],
            ]);
        }

        $alreadyGuessed = $game->relationLoaded('guesses')
            ? $game->guesses->contains('guess', $guess)
            : $game->guesses()->where('guess', $guess)->exists();

        if ($alreadyGuessed) {
            throw ValidationException::withMessages([
                'guess' => [__('You have already tried that word.')],
            ]);
        }
    }

    /**
     * Find or create a game.
     *
     * @param User|null      $user
     * @param string|null    $guestToken
     * @param int            $wordID
     * @param int|null       $dailyPuzzleID
     * @param GameMode $mode
     *
     * @return Game
     */
    protected static function findOrCreateGame(
        ?User $user,
        ?string $guestToken,
        int $wordID,
        ?int $dailyPuzzleID,
        GameMode $mode,
    ): Game {
        $query = Game::where('word_id', $wordID)
            ->where('mode', $mode->value);

        if ($dailyPuzzleID !== null) {
            $query->where('daily_puzzle_id', $dailyPuzzleID);
        }

        if ($user) {
            $query->where('user_id', $user->id);
        } else if ($guestToken) {
            $query->whereNull('user_id')->where('guest_token', $guestToken);
        } else {
            $query->whereRaw('1 = 0');
        }

        $existing = $query->first();

        if ($existing) {
            return $existing;
        }

        return self::createGame($user, $guestToken, $wordID, $dailyPuzzleID, $mode);
    }

    /**
     * Create a new game row.
     *
     * @param User|null      $user
     * @param string|null    $guestToken
     * @param int            $wordID
     * @param int|null       $dailyPuzzleID
     * @param GameMode $mode
     *
     * @return Game
     */
    protected static function createGame(
        ?User $user,
        ?string $guestToken,
        int $wordID,
        ?int $dailyPuzzleID,
        GameMode $mode,
    ): Game {
        if (!$user && !$guestToken) {
            throw ValidationException::withMessages([
                'guest_token' => [__('A user or guest token is required to start a game.')],
            ]);
        }

        $game = new Game();
        $game->user_id = $user?->id;
        $game->guest_token = $user ? null : $guestToken;
        $game->word_id = $wordID;
        $game->daily_puzzle_id = $dailyPuzzleID;
        $game->mode = $mode;
        $game->status = GameStatus::InProgress();
        $game->started_at = Carbon::now();
        $game->save();

        return $game;
    }

    /**
     * Generate a seed for a versus game.
     *
     * @return string
     */
    protected static function generateVersusSeed(): string
    {
        do {
            $seed = substr(str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(16))), 0, 22);
        } while (Game::where('versus_seed', $seed)->exists());

        return $seed;
    }
}
