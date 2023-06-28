<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SongType Opening()
 * @method static SongType Ending()
 * @method static SongType Background()
 */
final class SongType extends Enum
{
    const Opening       = 0;
    const Ending        = 1;
    const Background    = 2;

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
