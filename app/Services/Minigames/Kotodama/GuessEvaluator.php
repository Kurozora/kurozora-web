<?php

namespace App\Services\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\Feedback;
use InvalidArgumentException;

class GuessEvaluator
{
    /**
     * The feedback a guess earns against an answer.
     *
     * @param string $guess
     * @param string $answer
     *
     * @return string
     */
    public static function evaluate(string $guess, string $answer): string
    {
        $guess = strtolower($guess);
        $answer = strtolower($answer);

        if (mb_strlen($guess) !== mb_strlen($answer)) {
            throw new InvalidArgumentException('Guess and answer must be the same length.');
        }

        $length = mb_strlen($answer);
        $feedback = array_fill(0, $length, Feedback::Miss);
        $remaining = [];

        for ($i = 0; $i < $length; $i++) {
            $guessChar = mb_substr($guess, $i, 1);
            $answerChar = mb_substr($answer, $i, 1);

            if ($guessChar === $answerChar) {
                $feedback[$i] = Feedback::Hit;
                continue;
            }

            $remaining[$answerChar] = ($remaining[$answerChar] ?? 0) + 1;
        }

        for ($i = 0; $i < $length; $i++) {
            if ($feedback[$i] === Feedback::Hit) {
                continue;
            }

            $guessChar = mb_substr($guess, $i, 1);

            if (($remaining[$guessChar] ?? 0) > 0) {
                $feedback[$i] = Feedback::Present;
                $remaining[$guessChar]--;
            }
        }

        return implode('', $feedback);
    }
}
