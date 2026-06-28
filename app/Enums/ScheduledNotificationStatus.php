<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ScheduledNotificationStatus Pending()
 * @method static ScheduledNotificationStatus Dispatched()
 * @method static ScheduledNotificationStatus Cancelled()
 * @method static ScheduledNotificationStatus Superseded()
 */
final class ScheduledNotificationStatus extends Enum
{
    const int Pending = 0;
    const int Dispatched = 1;
    const int Cancelled = 2;
    const int Superseded = 3;
}
