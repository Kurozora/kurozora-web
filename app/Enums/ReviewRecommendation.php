<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ReviewRecommendation NotRecommended()
 * @method static ReviewRecommendation MixedFeelings()
 * @method static ReviewRecommendation Recommended()
 */
final class ReviewRecommendation extends Enum
{
    const int NotRecommended = 0;
    const int MixedFeelings = 1;
    const int Recommended = 2;

    /**
     * The recommendations in the order they are offered to the reviewer.
     *
     * @return array
     */
    public static function offeredOrder(): array
    {
        return [
            self::Recommended(),
            self::MixedFeelings(),
            self::NotRecommended(),
        ];
    }

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::NotRecommended => __('Not Recommended'),
            self::MixedFeelings => __('Mixed Feelings'),
            self::Recommended => __('Recommended'),
        };
    }
}
