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
    const string Small     = 'small';
    const string Medium    = 'medium';
    const string Large     = 'large';
    const string Video     = 'video';
    const string Upcoming  = 'upcoming';
}
