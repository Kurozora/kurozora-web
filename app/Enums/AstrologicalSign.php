<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;
use Carbon\Carbon;

/**
 * @method static AstrologicalSign Aries()
 * @method static AstrologicalSign Taurus()
 * @method static AstrologicalSign Gemini()
 * @method static AstrologicalSign Cancer()
 * @method static AstrologicalSign Leo()
 * @method static AstrologicalSign Virgo()
 * @method static AstrologicalSign Libra()
 * @method static AstrologicalSign Scorpio()
 * @method static AstrologicalSign Sagittarius()
 * @method static AstrologicalSign Capricorn()
 * @method static AstrologicalSign Aquarius()
 * @method static AstrologicalSign Pisces()
 */
final class AstrologicalSign extends Enum
{
    const int Aries         = 0;
    const int Taurus        = 1;
    const int Gemini        = 2;
    const int Cancer        = 3;
    const int Leo           = 4;
    const int Virgo         = 5;
    const int Libra         = 6;
    const int Scorpio       = 7;
    const int Sagittarius   = 8;
    const int Capricorn     = 9;
    const int Aquarius      = 10;
    const int Pisces        = 11;

    /**
     * The emojis representing the astrological signs.
     *
     * @var array
     */
    protected static array $signsEmoji = [
        '♈️',
        '♉️',
        '♊️',
        '♋️',
        '♌️',
        '♍️',
        '♎️',
        '♏️',
        '♐️',
        '♑️',
        '♒️',
        '♓️'
    ];

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        if (!isset($value)) {
            return '';
        }

        return parent::getDescription((int) $value) . ' ' . self::$signsEmoji[(int) $value];
    }

    public static function getFromDate(Carbon $date): AstrologicalSign
    {
        $day = $date->day;
        $month = $date->month;

        if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) {
            return self::Aquarius();
        }
        if (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) {
            return self::Pisces();
        }
        if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) {
            return self::Aries();
        }
        if (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) {
            return self::Taurus();
        }
        if (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) {
            return self::Gemini();
        }
        if (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) {
            return self::Cancer();
        }
        if (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) {
            return self::Leo();
        }
        if (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) {
            return self::Virgo();
        }
        if (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) {
            return self::Libra();
        }
        if (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) {
            return self::Scorpio();
        }
        if (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) {
            return self::Sagittarius();
        }

//        if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19))
            return self::Capricorn();
    }
}
