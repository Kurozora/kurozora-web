<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Opening()
 * @method static static Ending()
 * @method static static Background()
 */
final class SongType extends Enum
{
    const Opening       =   1;
    const Ending        =   2;
    const Background    =   3;

    /**
     * Return the abbreviated string for the SongType type.
     *
     * @return string
     */
    public function abbreviated(): string
    {
        return match ($this->value) {
            SongType::Ending => 'ED',
            SongType::Background => 'BG',
            default => 'OP'
        };
    }

    /**
     * Returns the color value associated with the SongType type.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this->value) {
            SongType::Ending => 'bg-red-500',
            SongType::Background => 'bg-yellow-400',
            default => 'bg-blue-500'
        };
    }
}
