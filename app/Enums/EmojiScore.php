<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\MediaRating;
use BenSampo\Enum\Enum;

/**
 * @method static EmojiScore Disliked()
 * @method static EmojiScore Neutral()
 * @method static EmojiScore Liked()
 */
final class EmojiScore extends Enum
{
    const int Disliked = -1;
    const int Neutral = 0;
    const int Liked = 1;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::Disliked => __('Disliked it'),
            self::Neutral => __('It was okay'),
            self::Liked => __('Loved it'),
        };
    }

    /**
     * The rating the emoji score maps to.
     *
     * @return float
     */
    public function score(): float
    {
        return match ($this->value) {
            self::Disliked => 1.0,
            self::Neutral => 2.5,
            self::Liked => 4.5,
        };
    }

    /**
     * The emoji representing the score.
     *
     * @return string
     */
    public function emoji(): string
    {
        return match ($this->value) {
            self::Disliked => '🙁',
            self::Neutral => '😐',
            self::Liked => '😄',
        };
    }

    /**
     * The emoji score the given rating maps to.
     *
     * @param float|null $rating
     *
     * @return EmojiScore|null
     */
    public static function fromRating(?float $rating): ?EmojiScore
    {
        if ($rating === null || $rating <= MediaRating::MIN_RATING_VALUE) {
            return null;
        }

        return match (true) {
            $rating <= 1.75 => self::Disliked(),
            $rating <= 3.25 => self::Neutral(),
            default => self::Liked(),
        };
    }
}
