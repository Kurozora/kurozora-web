<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static WatchRating Unknown()
 * @method static WatchRating TVY()
 * @method static WatchRating TVY7()
 * @method static WatchRating TVY7FV()
 * @method static WatchRating TVG()
 * @method static WatchRating TVPG()
 * @method static WatchRating TV14()
 * @method static WatchRating TVMA()
 */
final class WatchRating extends Enum
{
    const Unknown   = 0;
    const TVY       = 1;
    const TVY7      = 2;
    const TVY7FV    = 3;
    const TVG       = 4;
    const TVPG      = 5;
    const TV14      = 6;
    const TVMA      = 7;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        switch($value) {
        case self::TVY:
            return 'TV-Y';
        case self::TVY7:
            return 'TV-Y7';
        case self::TVY7FV:
            return 'TV-Y7 (FV)';
        case self::TVG:
            return 'TV-G';
        case self::TVPG:
            return 'TV-PG';
        case self::TV14:
            return 'TV-14';
        case self::TVMA:
            return 'TV-MA';
        }
        return parent::getDescription($value);
    }
}
