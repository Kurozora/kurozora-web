<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Small()
 * @method static static Medium()
 * @method static static Large()
 * @method static static Video()
 */
final class ExploreCategorySize extends Enum
{
    const Small     = 'small';
    const Medium    = 'medium';
    const Large     = 'large';
    const Video     = 'video';
}
