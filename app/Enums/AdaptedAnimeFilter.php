<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AdaptedAnimeFilter All()
 * @method static AdaptedAnimeFilter Airing()
 * @method static AdaptedAnimeFilter Upcoming()
 */
final class AdaptedAnimeFilter extends Enum
{
    const int All = 0;
    const int Airing = 1;
    const int Upcoming = 2;

    /**
     * Returns the description of the filter.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::Airing => 'Airing Now',
            self::Upcoming => 'Upcoming Anime',
            default => parent::getDescription($value),
        };
    }
}
