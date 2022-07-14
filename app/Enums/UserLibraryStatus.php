<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserLibraryStatus Watching()
 * @method static UserLibraryStatus Planning()
 * @method static UserLibraryStatus Completed()
 * @method static UserLibraryStatus OnHold()
 * @method static UserLibraryStatus Dropped()
 */
final class UserLibraryStatus extends Enum
{
    const Watching  = 0;
    const Planning  = 2;
    const Completed = 3;
    const OnHold    = 4;
    const Dropped   = 1;

    /**
     * Returns the description of the status
     *
     * @param int|string $value
     * @return string
     */
    static function getDescription($value): string
    {
        return match ((int) $value) {
            self::OnHold => 'On-Hold',
            default => parent::getDescription((int) $value),
        };
    }

    /**
     * Returns an error displaying the valid library statuses
     *
     * @return string
     */
    static function error(): string
    {
        return 'Pick a valid library status: ' .
            implode(', ', self::getKeys())
        ;
    }
}
