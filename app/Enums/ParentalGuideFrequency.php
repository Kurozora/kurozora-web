<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideFrequency Brief()
 * @method static ParentalGuideFrequency Occasional()
 * @method static ParentalGuideFrequency Frequent()
 */
final class ParentalGuideFrequency extends Enum
{
    const int Brief = 1;
    const int Occasional = 2;
    const int Frequent = 3;
}
