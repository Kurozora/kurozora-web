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
    public static function abbreviated(int $value) {
        switch ($value) {
            case self::Sunday:
                return 'SU';
            case self::Monday:
                return 'MO';
            case self::Tuesday:
                return 'TU';
            case self::Wednesday:
                return 'WE';
            case self::Thursday:
                return 'TH';
            case self::Friday:
                return 'FR';
            case self::Saturday:
                return 'SA';
        }
    }
}
