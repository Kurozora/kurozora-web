<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\Feedback;
use App\Enums\Minigames\Kotodama\GameMode;
use App\Enums\Minigames\Kotodama\GameStatus;
use App\Models\Minigames\Kotodama\Game;

class ShareGridFormatter
{
    const string HIT_EMOJI     = '🟧';
    const string PRESENT_EMOJI = '🟪';
    const string MISS_EMOJI    = '⬛';

    /**
     * The emoji grid for a finished game.
     *
     * @param Game $game
     *
     * @return string
     */
    public static function format(Game $game): string
    {
        $game->loadMissing(['guesses', 'dailyPuzzle', 'word']);

        $lines = [self::headerFor($game), ''];

        foreach ($game->guesses as $guess) {
            $lines[] = self::rowFor($guess->feedback);
        }

        return implode("\n", $lines);
    }

    /**
     * The header line for the grid.
     *
     * @param Game $game
     *
     * @return string
     */
    protected static function headerFor(Game $game): string
    {
        $puzzleNumber = $game->dailyPuzzle?->puzzle_number;
        $guessScore = match (true) {
            $game->status?->is(GameStatus::Won) => $game->guess_count . '/' . Game::MAX_GUESSES,
            default => 'X/' . Game::MAX_GUESSES,
        };

        if ($puzzleNumber && $game->mode?->is(GameMode::Daily)) {
            return __(':x Kotodama #:number :score', [
                'x' => config('app.name'),
                'number' => $puzzleNumber,
                'score' => $guessScore,
            ]);
        }

        return __(':x Kotodama :score', [
            'x' => config('app.name'),
            'score' => $guessScore,
        ]);
    }

    /**
     * The emoji row for a feedback string.
     *
     * @param string $feedback
     *
     * @return string
     */
    protected static function rowFor(string $feedback): string
    {
        $row = '';

        for ($i = 0; $i < mb_strlen($feedback); $i++) {
            $row .= match (mb_substr($feedback, $i, 1)) {
                Feedback::Hit => self::HIT_EMOJI,
                Feedback::Present => self::PRESENT_EMOJI,
                default => self::MISS_EMOJI,
            };
        }

        return $row;
    }
}
