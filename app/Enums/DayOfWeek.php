<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static DayOfWeek Sunday()
 * @method static DayOfWeek Monday()
 * @method static DayOfWeek Tuesday()
 * @method static DayOfWeek Wednesday()
 * @method static DayOfWeek Thursday()
 * @method static DayOfWeek Friday()
 * @method static DayOfWeek Saturday()
 */
final class DayOfWeek extends Enum
{
    const Sunday    = 0;
    const Monday    = 1;
    const Tuesday   = 2;
    const Wednesday = 3;
    const Thursday  = 4;
    const Friday    = 5;
    const Saturday  = 6;

    /**
     * Return the abbreviated string for the DayOfWeek type.
     *
     * @param int $value
     * @return string
     */
    public static function abbreviated(int $value): string
    {
        return match ($value) {
            self::Sunday => 'SU',
            self::Monday => 'MO',
            self::Tuesday => 'TU',
            self::Wednesday => 'WE',
            self::Thursday => 'TH',
            self::Friday => 'FR',
            default => 'SA',
        };
    }
}
