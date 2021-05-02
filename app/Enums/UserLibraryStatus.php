<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserLibraryStatus extends Enum
{
    const Watching  = 0;
    const Dropped   = 1;
    const Planning  = 2;
    const Completed = 3;
    const OnHold    = 4;

    /**
     * Returns the description of the status
     *
     * @param int|string $value
     * @return string
     */
    static function getDescription($value): string
    {
        return match ($value) {
            self::OnHold => 'On-Hold',
            default => parent::getDescription($value),
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
