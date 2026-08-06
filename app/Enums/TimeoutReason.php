<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static TimeoutReason Spam()
 * @method static TimeoutReason Harassment()
 * @method static TimeoutReason NSFW()
 * @method static TimeoutReason Impersonation()
 * @method static TimeoutReason Hate()
 * @method static TimeoutReason Other()
 *
 * @template TValue
 */
final class TimeoutReason extends Enum
{
    const int Spam = 1;
    const int Harassment = 2;
    const int NSFW = 3;
    const int Impersonation = 4;
    const int Hate = 5;
    const int Other = 6;

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
            self::Spam => __('Spam'),
            self::Harassment => __('Harassment'),
            self::NSFW => __('NSFW'),
            self::Impersonation => __('Impersonation'),
            self::Hate => __('Hate'),
            self::Other => __('Other'),
            default => parent::getLocalizedDescription($value),
        };
    }
}
