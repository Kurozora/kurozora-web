<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static QuickReaction()
 * @method static static Standard()
 * @method static static Advanced()
 * @method static static Detailed()
 */
final class RatingStyle extends Enum
{
    const int QuickReaction = 0;
    const int Standard      = 1;
    const int Advanced      = 2;
    const int Detailed      = 3;

    /**
     * The integer value stored in the `users.rating_style` column.
     *
     * 0 = Quick Reaction  — emoji-based (-1 / 0 / 1 mapped to 2/5/9 out of 10)
     * 1 = Standard        — 5-star rating (stored as x*2 to fit 0–10 scale)
     * 2 = Advanced        — 10-star rating (stored as-is)
     * 3 = Detailed        — per-category scores; overall is calculated
     */
    public static function getDescription($value): string
    {
        return match ($value) {
            self::QuickReaction => 'Quick Reaction',
            self::Standard      => 'Standard',
            self::Advanced      => 'Advanced',
            self::Detailed      => 'Detailed Review',
            default             => parent::getDescription($value),
        };
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Map a quick-reaction input (-1, 0, 1) to a 0–10 database value.
     * 🙁 -1 → 2  |  😐 0 → 5  |  😄 1 → 9
     */
    public static function quickReactionToRating(int $reaction): float
    {
        return match ($reaction) {
            -1      => 2.0,
            0       => 5.0,
            1       => 9.0,
            default => throw new \InvalidArgumentException("Quick reaction must be -1, 0 or 1, got {$reaction}."),
        };
    }

    /**
     * Convert a 0–10 database value back to the nearest quick-reaction value.
     */
    public static function ratingToQuickReaction(float $rating): int
    {
        return match (true) {
            $rating <= 3.5 => -1,
            $rating <= 7.0 => 0,
            default        => 1,
        };
    }
}