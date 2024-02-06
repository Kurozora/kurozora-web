<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ImportService MAL()
 * @method static ImportService Kitsu()
 */
final class ImportService extends Enum
{
    const int MAL   =   0;
    const int Kitsu =   1;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::MAL => 'MyAnimeList',
            self::Kitsu => 'Kitsu',
            default => parent::getDescription((int) $value),
        };
    }
}
