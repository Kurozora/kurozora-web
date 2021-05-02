<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class DayOfWeek extends Enum
{
    const Sunday    = 1;
    const Monday    = 2;
    const Tuesday   = 3;
    const Wednesday = 4;
    const Thursday  = 5;
    const Friday    = 6;
    const Saturday  = 7;

    /**
     * Return the abbreviated string for the DayOfWeek type.
     *
     * @param int $value
     * @return string
     */
    public static function abbreviated(int $value): string
    {
        return match ($value) {
            self::Monday => 'MO',
            self::Tuesday => 'TU',
            self::Wednesday => 'WE',
            self::Thursday => 'TH',
            self::Friday => 'FR',
            self::Saturday => 'SA',
            default => 'SU',
        };
    }
}
