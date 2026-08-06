<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static RatingStyle QuickReaction()
 * @method static RatingStyle Standard()
 * @method static RatingStyle Detailed()
 */
final class RatingStyle extends Enum
{
    const int QuickReaction = 0;
    const int Standard = 1;
    const int Detailed = 2;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::QuickReaction => __('Quick Reaction'),
            self::Standard => __('Standard'),
            self::Detailed => __('Detailed Review'),
        };
    }
}
