<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ReportReason Spam()
 * @method static ReportReason NotAReview()
 * @method static ReportReason Spoiler()
 * @method static ReportReason Abuse()
 * @method static ReportReason Inappropriate()
 * @method static ReportReason SelfHarm()
 * @method static ReportReason Piracy()
 * @method static ReportReason Other()
 *
 * @template TValue
 */
final class ReportReason extends Enum
{
    const string Spam = 'spam';
    const string NotAReview = 'not_a_review';
    const string Spoiler = 'spoiler';
    const string Abuse = 'abuse';
    const string Inappropriate = 'inappropriate';
    const string SelfHarm = 'self_harm';
    const string Piracy = 'piracy';
    const string Other = 'other';

    /**
     * The reasons offered when reporting a review.
     *
     * @return array<array-key, string>
     */
    public static function offeredForReview(): array
    {
        return [
            self::NotAReview,
            self::Spam,
            self::Spoiler,
            self::Piracy,
            self::Abuse,
            self::Inappropriate,
            self::Other,
        ];
    }

    /**
     * The reasons offered when reporting a feed message.
     *
     * @return array<array-key, string>
     */
    public static function offeredForFeedMessage(): array
    {
        return [
            self::Spam,
            self::Spoiler,
            self::Piracy,
            self::Abuse,
            self::Inappropriate,
            self::SelfHarm,
            self::Other,
        ];
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
            self::Spam => __('Spam or advertising'),
            self::NotAReview => __('Not a real review'),
            self::Spoiler => __('Unmarked spoilers'),
            self::Abuse => __('Hate, harassment or threats'),
            self::Inappropriate => __('Inappropriate content'),
            self::SelfHarm => __('Suicide or self-harm'),
            self::Piracy => __('Unauthorized links'),
            self::Other => __('Something else'),
            default => ReportReason::getLocalizedDescription($value),
        };
    }
}
