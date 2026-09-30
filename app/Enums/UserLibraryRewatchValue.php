<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryRewatchValue VeryLow()
 * @method static UserLibraryRewatchValue Low()
 * @method static UserLibraryRewatchValue Medium()
 * @method static UserLibraryRewatchValue High()
 * @method static UserLibraryRewatchValue VeryHigh()
 */
final class UserLibraryRewatchValue extends Enum
{
    const int VeryLow = 0;
    const int Low = 1;
    const int Medium = 2;
    const int High = 3;
    const int VeryHigh = 4;
}
