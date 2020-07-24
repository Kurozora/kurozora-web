<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
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
    const TVY       = 0;
    const TVY7      = 1;
    const TVY7FV    = 2;
    const TVG       = 3;
    const TVPG      = 4;
    const TV14      = 5;
    const TVMA      = 6;

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
            break;
        case self::TVY7:
            return 'TV-Y7';
            break;
        case self::TVY7FV:
            return 'TV-Y7 (FV)';
            break;
        case self::TVG:
            return 'TV-G';
            break;
        case self::TVPG:
            return 'TV-PG';
            break;
        case self::TV14:
            return 'TV-14';
            break;
        case self::TVMA:
            return 'TV-MA';
            break;
        }
        return parent::getDescription($value);
    }
}
