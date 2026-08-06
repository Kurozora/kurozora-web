<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideReaction Unhelpful()
 * @method static ParentalGuideReaction Helpful()
 */
final class ParentalGuideReaction extends Enum
{
    const int Unhelpful = 0;
    const int Helpful   = 1;

    /**
     * Returns the next ParentalGuideReaction type.
     *
     * @return ParentalGuideReaction
     */
    public function next(): ParentalGuideReaction {
        return match ($this->value) {
            self::Unhelpful => self::Helpful(),
            default => self::Unhelpful(),
        };
    }

    /**
     * Returns the previous ParentalGuideReaction type.
     *
     * @return ParentalGuideReaction
     */
    public function previous(): ParentalGuideReaction {
        return match ($this->value) {
            self::Helpful => self::Unhelpful(),
            default => self::Helpful(),
        };
    }
}
