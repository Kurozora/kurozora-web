<?php

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
    const Aries         = 0;
    const Taurus        = 1;
    const Gemini        = 2;
    const Cancer        = 3;
    const Leo           = 4;
    const Virgo         = 5;
    const Libra         = 6;
    const Scorpio       = 7;
    const Sagittarius   = 8;
    const Capricorn     = 9;
    const Aquarius      = 10;
    const Pisces        = 11;

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
