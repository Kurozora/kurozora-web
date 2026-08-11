<?php

namespace App\Services\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Word;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use RuntimeException;

class PuzzleResolver
{
    const int SCHEDULE_LOOKBACK_DAYS = 180;
    const int HINT_DRAW_ATTEMPTS = 5;

    /**
     * Resolve today's puzzle.
     *
     * @return DailyPuzzle
     */
    public static function today(): DailyPuzzle
    {
        return self::forDate(Carbon::now()->toDateString());
    }

    /**
     * Resolve the daily puzzle for a past date.
     *
     * @param Carbon $date
     *
     * @return DailyPuzzle
     */
    public static function archive(Carbon $date): DailyPuzzle
    {
        return self::forDate($date->toDateString());
    }

    /**
     * The next word to schedule.
     *
     * @return Word
     */
    public static function pickNextWord(): Word
    {
        $recentlyUsedWordIDs = DailyPuzzle::where('puzzle_date', '>=', Carbon::now()
            ->subDays(self::SCHEDULE_LOOKBACK_DAYS)
            ->toDateString())
            ->pluck('word_id');

        $word = self::drawHintable(
            Word::eligibleForSchedule()
                ->when($recentlyUsedWordIDs->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $recentlyUsedWordIDs))
                ->with(['subject'])
        );

        if (!$word) {
            throw new RuntimeException('No eligible Kotodama word available.');
        }

        return $word;
    }

    /**
     * Draws a random word that can produce a hint.
     *
     * @param Builder $query
     *
     * @return Word|null
     */
    public static function drawHintable(Builder $query): ?Word
    {
        $word = null;

        for ($attempt = 0; $attempt < self::HINT_DRAW_ATTEMPTS; $attempt++) {
            $word = (clone $query)->randomFirst();

            if ($word === null || $word->getHint() !== null) {
                return $word;
            }
        }

        // Serving a word whose hint came back empty still beats refusing to start a game.
        return $word;
    }

    /**
     * Resolve the daily puzzle for a specific date.
     *
     * @param string $date
     *
     * @return DailyPuzzle
     */
    protected static function forDate(string $date): DailyPuzzle
    {
        $puzzle = DailyPuzzle::where('puzzle_date', $date)
            ->with(['word.subject'])
            ->first();

        if (!$puzzle) {
            throw (new ModelNotFoundException)->setModel(DailyPuzzle::class);
        }

        return $puzzle;
    }
}
