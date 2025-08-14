<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideRating None()
 * @method static ParentalGuideRating Mild()
 * @method static ParentalGuideRating Moderate()
 * @method static ParentalGuideRating Severe()
 */
final class ParentalGuideRating extends Enum
{
    const int None = 0;
    const int Mild = 1;
    const int Moderate = 2;
    const int Severe = 3;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::None => 'None',
            self::Mild => 'Mild',
            self::Moderate => 'Moderate',
            self::Severe => 'Severe',
        };
    }
}
