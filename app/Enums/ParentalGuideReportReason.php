<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideReportReason Inaccurate()
 * @method static ParentalGuideReportReason Spoiler()
 * @method static ParentalGuideReportReason Spam()
 * @method static ParentalGuideReportReason Inappropriate()
 * @method static ParentalGuideReportReason Other()
 *
 * @template TValue
 */
final class ParentalGuideReportReason extends Enum
{
    const string Inaccurate = 'inaccurate';
    const string Spoiler = 'spoiler';
    const string Spam = 'spam';
    const string Inappropriate = 'inappropriate';
    const string Other = 'other';

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
            self::Inaccurate => __('Inaccurate'),
            self::Spoiler => __('Spoiler'),
            self::Spam => __('Spam'),
            self::Inappropriate => __('Inappropriate'),
            self::Other => __('Other'),
            default => ParentalGuideReportReason::getLocalizedDescription($value),
        };
    }
}
