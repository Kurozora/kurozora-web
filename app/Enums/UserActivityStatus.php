<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserActivityStatus Online()
 * @method static UserActivityStatus SeenRecently()
 * @method static UserActivityStatus Offline()
 */
final class UserActivityStatus extends Enum
{
    const Online = 0;
    const SeenRecently = 1;
    const Offline = 2;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        return match ($value) {
            self::Online => 'Online',
            self::SeenRecently => 'Seen Recently',
            self::Offline => 'Offline',
            default => parent::getDescription($value),
        };
    }
}
