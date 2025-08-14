<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideDepiction Implied()
 * @method static ParentalGuideDepiction Shown()
 * @method static ParentalGuideDepiction Graphic()
 */
final class ParentalGuideDepiction extends Enum
{
    const int Implied = 1;
    const int Shown = 2;
    const int Graphic = 3;
}
