<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static RatingSentiment NotEnough()
 * @method static RatingSentiment OverwhelminglyPositive()
 * @method static RatingSentiment VeryPositive()
 * @method static RatingSentiment Positive()
 * @method static RatingSentiment MostlyPositive()
 * @method static RatingSentiment MixedFeelings()
 * @method static RatingSentiment MostlyNegative()
 * @method static RatingSentiment Negative()
 * @method static RatingSentiment VeryNegative()
 * @method static RatingSentiment OverwhelminglyNegative()
 */
final class RatingSentiment extends Enum
{
    const int NotEnough = 0;
    const int OverwhelminglyPositive = 1;
    const int Positive = 2;
    const int MixedFeelings = 4;
    const int OverwhelminglyNegative = 5;
    const int Negative = 6;
    const int VeryPositive = 7;
    const int MostlyPositive = 8;
    const int MostlyNegative = 9;
    const int VeryNegative = 10;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::NotEnough => __('Not enough ratings'),
            self::OverwhelminglyPositive => __('Rated Overwhelmingly Positive'),
            self::VeryPositive => __('Rated Very Positive'),
            self::Positive => __('Rated Positive'),
            self::MostlyPositive => __('Rated Mostly Positive'),
            self::MixedFeelings => __('Rated Mixed Feelings'),
            self::MostlyNegative => __('Rated Mostly Negative'),
            self::Negative => __('Rated Negative'),
            self::VeryNegative => __('Rated Very Negative'),
            self::OverwhelminglyNegative => __('Rated Overwhelmingly Negative'),
        };
    }
}
