<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static RatingSentiment NotEnough()
 * @method static RatingSentiment OverwhelminglyPositive()
 * @method static RatingSentiment OverwhelminglyNegative()
 * @method static RatingSentiment MixedFeelings()
 * @method static RatingSentiment Positive()
 * @method static RatingSentiment Negative()
 * @method static RatingSentiment Average()
 */
final class RatingSentiment extends Enum
{
    const int NotEnough = 0;
    const int OverwhelminglyPositive = 1;
    const int Positive = 2;
    const int Average = 3;
    const int MixedFeelings = 4;
    const int OverwhelminglyNegative = 5;
    const int Negative = 6;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::NotEnough => __('Not enough ratings'),
            self::OverwhelminglyPositive => __('Rated Overwhelmingly Positive'),
            self::Positive => __('Rated Positive'),
            self::Average => __('Rated Average'),
            self::MixedFeelings => __('Rated Mixed Feeling'),
            self::OverwhelminglyNegative => __('Rating Overwhelmingly Negative'),
            self::Negative => __('Rated Negative'),
        };
    }
}
