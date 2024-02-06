<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ImportBehavior Overwrite()
 * @method static ImportBehavior Merge()
 */
final class ImportBehavior extends Enum
{
    const int Overwrite = 0;
    const int Merge     = 1;
}
