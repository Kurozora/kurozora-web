<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

/**
 * @method static TimeoutDuration OneHour()
 * @method static TimeoutDuration OneDay()
 * @method static TimeoutDuration ThreeDays()
 * @method static TimeoutDuration SevenDays()
 * @method static TimeoutDuration ThirtyDays()
 * @method static TimeoutDuration Permanent()
 *
 * @template TValue
 */
final class TimeoutDuration extends Enum
{
    const int OneHour = 1;
    const int OneDay = 2;
    const int ThreeDays = 3;
    const int SevenDays = 4;
    const int ThirtyDays = 5;
    const int Permanent = 6;

    /**
     * Resolve the corresponding `CarbonInterval`, or `null` for a permanent ban.
     *
     * @param int $value
     *
     * @return CarbonInterval|null
     */
    public static function intervalFor(int $value): ?CarbonInterval
    {
        return match ($value) {
            self::OneHour => CarbonInterval::hour(),
            self::OneDay => CarbonInterval::day(),
            self::ThreeDays => CarbonInterval::days(3),
            self::SevenDays => CarbonInterval::days(7),
            self::ThirtyDays => CarbonInterval::days(30),
            self::Permanent => null,
        };
    }

    /**
     * Resolve the absolute expiry timestamp for the given duration, anchored to now.
     *
     * @param int $value
     *
     * @return CarbonImmutable|null
     */
    public static function expiresAtFor(int $value): ?CarbonImmutable
    {
        $interval = self::intervalFor($value);

        if ($interval === null) {
            return null;
        }

        return CarbonImmutable::now()->add($interval);
    }

    /**
     * Get the localized description of a value.
     *
     * @param TValue $value
     *
     * @return string|null
     */
    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::OneHour => __('1 hour'),
            self::OneDay => __('1 day'),
            self::ThreeDays => __('3 days'),
            self::SevenDays => __('7 days'),
            self::ThirtyDays => __('30 days'),
            self::Permanent => __('Permanent'),
            default => parent::getLocalizedDescription($value),
        };
    }
}
