<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

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
}
