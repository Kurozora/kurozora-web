<?php

namespace App\Services\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Word;

class GuessDictionary
{
    /**
     * The allowed-guess words, keyed by word.
     *
     * @var array|null
     */
    protected static ?array $words = null;

    /**
     * Whether the given guess is an allowed word.
     *
     * @param string $guess
     *
     * @return bool
     */
    public static function contains(string $guess): bool
    {
        if (self::$words === null) {
            $wordList = file(resource_path('data/kotodama/guess-list.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            self::$words = array_fill_keys($wordList ?: [], true);
        }

        if (isset(self::$words[$guess])) {
            return true;
        }

        return Word::where('answer', $guess)->exists();
    }
}
