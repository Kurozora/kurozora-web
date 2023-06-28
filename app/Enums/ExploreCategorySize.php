<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Small()
 * @method static static Medium()
 * @method static static Large()
 * @method static static Video()
 * @method static static Upcoming()
 */
final class ExploreCategorySize extends Enum
{
    const Small     = 'small';
    const Medium    = 'medium';
    const Large     = 'large';
    const Video     = 'video';
    const Upcoming  = 'upcoming';
}
