<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static WeeklyDigestSection Drops()
 * @method static WeeklyDigestSection Recommendations()
 * @method static WeeklyDigestSection Rescue()
 * @method static WeeklyDigestSection UpNext()
 * @method static WeeklyDigestSection Trending()
 * @method static WeeklyDigestSection Birthdays()
 * @method static WeeklyDigestSection Momentum()
 * @method static WeeklyDigestSection Growth()
 */
final class WeeklyDigestSection extends Enum
{
    const string Drops = 'drops';
    const string Recommendations = 'recommendations';
    const string Rescue = 'rescue';
    const string UpNext = 'up-next';
    const string Trending = 'trending';
    const string Birthdays = 'birthdays';
    const string Momentum = 'momentum';
    const string Growth = 'growth';
}
