<?php

namespace App\Models\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\Feedback;
use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\KModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends KModel
{
    // Table name
    const string TABLE_NAME = 'kotodama_games';
    protected $table = self::TABLE_NAME;

    const int MAX_GUESSES = 6;
    const int HINT_REVEAL_THRESHOLD = 3;
    const int SUBJECT_REVEAL_THRESHOLD = 5;

    /**
     * The keyboard states.
     *
     * @var array|null
     */
    protected ?array $keyboardStates = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'mode'        => GameMode::class,
            'status'      => GameStatus::class,
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
            'guess_count' => 'int',
            'duration_ms' => 'int',
        ];
    }

    /**
     * Returns the user that played this game.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the word that this game is playing against.
     *
     * @return BelongsTo
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    /**
     * Returns the daily puzzle this game belongs to.
     *
     * @return BelongsTo
     */
    public function dailyPuzzle(): BelongsTo
    {
        return $this->belongsTo(DailyPuzzle::class, 'daily_puzzle_id');
    }

    /**
     * Returns the guesses submitted against this game.
     *
     * @return HasMany
     */
    public function guesses(): HasMany
    {
        return $this->hasMany(Guess::class, 'game_id')
            ->orderBy('position');
    }

    /**
     * Whether this game has been resolved.
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->status !== null
            && !$this->status->is(GameStatus::InProgress);
    }

    /**
     * Whether this game's answer should be visible yet.
     *
     * @return bool
     */
    public function shouldRevealAnswer(): bool
    {
        return $this->status !== null
            && ($this->status->is(GameStatus::Won)
                || $this->status->is(GameStatus::Lost));
    }

    /**
     * Whether this game's hint should be visible yet.
     *
     * @return bool
     */
    public function shouldRevealHint(): bool
    {
        return $this->shouldRevealAnswer()
            || (int) $this->guess_count >= self::HINT_REVEAL_THRESHOLD;
    }

    /**
     * Whether this game's subject image should be visible yet.
     *
     * @return bool
     */
    public function shouldRevealSubject(): bool
    {
        return $this->shouldRevealAnswer()
            || (int) $this->guess_count >= self::SUBJECT_REVEAL_THRESHOLD;
    }

    /**
     * Returns the board as rows of cells.
     *
     * @return array
     */
    public function boardRows(): array
    {
        $rows = [];

        foreach ($this->guesses as $guess) {
            $rows[] = [
                'isActive' => false,
                'cells' => self::cellsFor((string) $guess->guess, (string) $guess->feedback),
            ];
        }

        $activeRow = count($rows);

        while (count($rows) < self::MAX_GUESSES) {
            $rows[] = [
                'isActive' => count($rows) === $activeRow && !$this->isFinished(),
                'cells' => self::cellsFor('', ''),
            ];
        }

        return $rows;
    }

    /**
     * Returns the best feedback earned for each guessed letter.
     *
     * @return array
     */
    public function keyboardStates(): array
    {
        if ($this->keyboardStates !== null) {
            return $this->keyboardStates;
        }

        $states = [];

        foreach ($this->guesses as $guess) {
            foreach (self::cellsFor((string) $guess->guess, (string) $guess->feedback) as $cell) {
                $letter = $cell['letter'];

                if ($letter === null || ($states[$letter] ?? null) === Feedback::Hit) {
                    continue;
                }

                if ($cell['feedback'] === Feedback::Hit
                    || ($cell['feedback'] === Feedback::Present && ($states[$letter] ?? null) !== Feedback::Hit)
                    || !array_key_exists($letter, $states)) {
                    $states[$letter] = $cell['feedback'];
                }
            }
        }

        return $this->keyboardStates = $states;
    }

    /**
     * Returns one cell per column of a guess.
     *
     * @param string $guess
     * @param string $feedback
     *
     * @return array
     */
    protected static function cellsFor(string $guess, string $feedback): array
    {
        $cells = [];

        for ($column = 0; $column < Word::LENGTH; $column++) {
            $cells[] = [
                'letter' => mb_substr($guess, $column, 1) ?: null,
                'feedback' => mb_substr($feedback, $column, 1) ?: null,
            ];
        }

        return $cells;
    }

    /**
     * Returns the hint once earned.
     *
     * @return string|null
     */
    public function revealedHint(): ?string
    {
        return $this->shouldRevealHint() && !$this->shouldRevealAnswer()
            ? $this->word?->getHint()
            : null;
    }

    /**
     * Returns the subject image URL once earned.
     *
     * @return string|null
     */
    public function revealedSubjectImageUrl(): ?string
    {
        return $this->shouldRevealSubject() && !$this->shouldRevealAnswer()
            ? $this->word?->getHintImageUrl()
            : null;
    }
}
