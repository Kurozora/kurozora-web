<?php

namespace App\Support;

use IntlBreakIterator;

class LowEffortReviewDetector
{
    /**
     * The share of non-letter characters above which the text reads as art rather than prose.
     */
    const float MAX_SYMBOL_SHARE = 0.5;

    /**
     * The longest run of one repeated character that still reads as writing.
     */
    const int MAX_CHARACTER_RUN = 5;

    /**
     * The number of words below which the text carries no argument.
     */
    const int MIN_WORD_COUNT = 3;

    /**
     * The number of words above which repetition becomes measurable.
     */
    const int REPETITION_WORD_COUNT = 6;

    /**
     * The share of distinct words below which the text is padding.
     */
    const float MIN_DISTINCT_WORD_SHARE = 0.35;

    /**
     * Indicates whether the review reads as a meme, art, or a one-word joke.
     *
     * Every signal is language-agnostic, so the verdict holds across every supported
     * language. It demotes rather than blocks, so it is tuned for precision.
     *
     * @param null|string $description
     *
     * @return bool
     */
    public static function detect(?string $description): bool
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $description));

        if ($text === '') {
            return false;
        }

        if (self::hasNoLetters($text)) {
            return true;
        }

        if (self::symbolShare($text) > self::MAX_SYMBOL_SHARE) {
            return true;
        }

        if (self::longestCharacterRun($text) > self::MAX_CHARACTER_RUN) {
            return true;
        }

        $words = self::words($text);
        $wordCount = count($words);

        if ($wordCount < self::MIN_WORD_COUNT) {
            return true;
        }

        if ($wordCount >= self::REPETITION_WORD_COUNT) {
            $distinctShare = count(array_unique($words)) / $wordCount;

            if ($distinctShare < self::MIN_DISTINCT_WORD_SHARE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicates whether the text holds no letter in any script.
     *
     * @param string $text
     *
     * @return bool
     */
    protected static function hasNoLetters(string $text): bool
    {
        return preg_match('/\p{L}/u', $text) !== 1;
    }

    /**
     * Returns the share of characters that are neither letters nor digits.
     *
     * @param string $text
     *
     * @return float
     */
    protected static function symbolShare(string $text): float
    {
        $characters = preg_split('//u', str_replace(' ', '', $text), -1, PREG_SPLIT_NO_EMPTY);
        $total = count($characters);

        if ($total === 0) {
            return 0.0;
        }

        $letters = preg_match_all('/[\p{L}\p{N}]/u', implode('', $characters));

        return ($total - $letters) / $total;
    }

    /**
     * Returns the length of the longest run of one repeated character.
     *
     * @param string $text
     *
     * @return int
     */
    protected static function longestCharacterRun(string $text): int
    {
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $longest = 0;
        $run = 0;
        $previous = null;

        foreach ($characters as $character) {
            $run = $character === $previous ? $run + 1 : 1;
            $previous = $character;
            $longest = max($longest, $run);
        }

        return $longest;
    }

    /**
     * Returns the text's words, cased down.
     *
     * Segmentation goes through ICU so scripts that do not separate words with spaces
     * are counted correctly.
     *
     * @param string $text
     *
     * @return array
     */
    protected static function words(string $text): array
    {
        $iterator = IntlBreakIterator::createWordInstance(null);
        $iterator->setText($text);

        $words = [];

        foreach ($iterator->getPartsIterator() as $part) {
            if (preg_match('/[\p{L}\p{N}]/u', $part) === 1) {
                $words[] = mb_strtolower($part);
            }
        }

        return $words;
    }
}
