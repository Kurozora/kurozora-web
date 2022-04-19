<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ImportService MAL()
 * @method static ImportService Kitsu()
 */
final class ImportService extends Enum
{
    const MAL   =   0;
    const Kitsu =   1;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        return match ((int) $value) {
            self::MAL => 'MyAnimeList',
            self::Kitsu => 'Kitsu',
            default => parent::getDescription((int) $value),
        };
    }
}
