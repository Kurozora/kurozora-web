<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AnimeStatus TBA()
 * @method static AnimeStatus Ended()
 * @method static AnimeStatus Continuing()
 */
final class AnimeStatus extends Enum
{
    const TBA           = 0;
    const Ended         = 1;
    const Continuing    = 2;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        return match ($value) {
            self::TBA => 'To Be Announced',
            self::Ended => 'Finished Airing',
            self::Continuing => 'Currently Airing',
            default => parent::getDescription($value),
        };
    }

    /**
     * The color class of the anime status.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this->value) {
            self::TBA => 'amber',
            self::Ended => 'red',
            self::Continuing => 'green',
            default => 'gray',
        };
    }
}
