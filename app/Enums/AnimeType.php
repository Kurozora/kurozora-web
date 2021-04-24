<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AnimeType extends Enum
{
    const Unknown   = 0;
    const TV        = 1;
    const Movie     = 2;
    const Music     = 3;
    const ONA       = 4;
    const OVA       = 5;
    const Special   = 6;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::TV:
                return 'TV';
            case self::ONA:
                return 'Original Net Animation';
            case self::OVA:
                return 'Original Video Animation';
        }

        return parent::getDescription($value);
    }
}
