<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ImportBehavior Overwrite()
 * @method static ImportBehavior Merge()
 */
final class ImportBehavior extends Enum
{
    const Overwrite =   0;
    const Merge     =   1;
}
