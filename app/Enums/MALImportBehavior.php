<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static MALImportBehavior Overwrite()
 * @method static MALImportBehavior Merge()
 */
final class MALImportBehavior extends Enum
{
    const Overwrite =   0;
    const Merge     =   1;
}
