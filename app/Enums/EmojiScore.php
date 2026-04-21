<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EmojiScore extends Enum
{
    const int DISLIKED = -1;
    const int NEUTRAL  = 0;
    const int LIKED    = 1;

    public static function score(int $value): float
    {
        return match ($value) {
            self::DISLIKED => 2.0,
            self::NEUTRAL  => 5.0,
            self::LIKED    => 8.0,
            default => 0.0,
        };
    }

    public static function emoji(int $value): string
    {
        return match ($value) {
            self::DISLIKED => '🙁',
            self::NEUTRAL  => '😐',
            self::LIKED    => '😄',
            default => '',
        };
    }

    public static function label(int $value): string
    {
        return match ($value) {
            self::DISLIKED => 'Disliked',
            self::NEUTRAL  => 'Neutral',
            self::LIKED    => 'Liked',
            default => '',
        };
    }
}