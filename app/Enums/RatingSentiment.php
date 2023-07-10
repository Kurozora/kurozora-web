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
    const NotEnough = 0;
    const OverwhelminglyPositive = 1;
    const Positive = 2;
    const Average = 3;
    const MixedFeelings = 4;
    const OverwhelminglyNegative = 5;
    const Negative = 6;

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
