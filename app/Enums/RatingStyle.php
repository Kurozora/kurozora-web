<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static RatingStyle QuickReaction()
 * @method static RatingStyle Standard()
 * @method static RatingStyle Advanced()
 * @method static RatingStyle Detailed()
 */
final class RatingStyle extends Enum
{
    const int QuickReaction = 0;
    const int Standard = 1;
    const int Advanced = 2;
    const int Detailed = 3;

    /**
     * Get the localized description for each rating style.
     *
     * @param mixed $value
     * @return string|null
     */
    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::QuickReaction => __('Quick Reaction'),
            self::Standard => __('Standard (5 Stars)'),
            self::Advanced => __('Advanced (10 Stars)'),
            self::Detailed => __('Detailed Review'),
        };
    }

    /**
     * Convert a quick reaction value (-1, 0, 1) to a 0-10 rating.
     *
     * @param int $reaction -1 (sad), 0 (neutral), 1 (happy)
     * @return float
     */
    public static function quickReactionToRating(int $reaction): float
    {
        return match ($reaction) {
            -1 => 2.0,  // Sad -> 2/10
            0 => 5.0,   // Neutral -> 5/10
            1 => 9.0,   // Happy -> 9/10
            default => 5.0,
        };
    }

    /**
     * Convert a 0-10 rating to a quick reaction value.
     *
     * @param float $rating
     * @return int -1 (sad), 0 (neutral), 1 (happy)
     */
    public static function ratingToQuickReaction(float $rating): int
    {
        if ($rating <= 3.5) {
            return -1; // Sad
        } elseif ($rating <= 6.5) {
            return 0; // Neutral
        }
        return 1; // Happy
    }

    /**
     * Get the emoji for a quick reaction value.
     *
     * @param int $reaction
     * @return string
     */
    public static function getQuickReactionEmoji(int $reaction): string
    {
        return match ($reaction) {
            -1 => '😞',
            0 => '😐',
            1 => '😄',
            default => '😐',
        };
    }

    /**
     * Convert standard rating (0-5) to internal rating (0-10).
     *
     * @param float $standardRating
     * @return float
     */
    public static function standardToInternal(float $standardRating): float
    {
        return $standardRating * 2.0;
    }

    /**
     * Convert internal rating (0-10) to standard rating (0-5).
     *
     * @param float $internalRating
     * @return float
     */
    public static function internalToStandard(float $internalRating): float
    {
        return $internalRating / 2.0;
    }
}
