<?php

namespace App\Services\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Word;
use Illuminate\Support\Str;

class AnswerTokenizer
{
    /**
     * Tokens that classify or locate a subject rather than name it.
     *
     * @var array
     */
    const array STOP_WORDS = [
        'anime', 'asian', 'bonus', 'china', 'datta', 'extra', 'fifth', 'films',
        'final', 'first', 'group', 'house', 'image', 'japan', 'korea', 'kyoto',
        'label', 'media', 'movie', 'north', 'osaka', 'other', 'parts', 'promo',
        'recap', 'shika', 'shita', 'shite', 'sixth', 'sound', 'south', 'tachi',
        'third', 'tokyo', 'uncut', 'video', 'works', 'xviii', 'xxiii',
    ];

    /**
     * The distinct answers a value can supply.
     *
     * @param string $subject
     *
     * @return array
     */
    public static function extract(string $subject): array
    {
        $normalized = Str::lower(Str::ascii($subject));
        $tokens = preg_split('/[^a-z]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $answers = [];

        foreach ($tokens as $token) {
            if (self::isEligible($token)) {
                $answers[$token] = $token;
            }
        }

        return array_values($answers);
    }

    /**
     * The whole value as an answer.
     *
     * @param string $subject
     *
     * @return string|null
     */
    public static function whole(string $subject): ?string
    {
        $normalized = Str::lower(Str::ascii($subject));
        $words = preg_split('/[^a-z]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) !== 1) {
            return null;
        }

        return self::isEligible($words[0]) ? $words[0] : null;
    }

    /**
     * Whether a token qualifies as an answer.
     *
     * @param string $token
     *
     * @return bool
     */
    public static function isEligible(string $token): bool
    {
        if (strlen($token) !== Word::LENGTH) {
            return false;
        }

        if (in_array($token, self::STOP_WORDS, true)) {
            return false;
        }

        return (bool) preg_match('/[aeiouy]/', $token);
    }
}
