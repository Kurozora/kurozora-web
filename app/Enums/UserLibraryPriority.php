<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryPriority Low()
 * @method static UserLibraryPriority Medium()
 * @method static UserLibraryPriority High()
 */
final class UserLibraryPriority extends Enum
{
    const int Low = 0;
    const int Medium = 1;
    const int High = 2;
}
