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
        return match ((int) $value) {
            self::Online => __('Online'),
            self::SeenRecently => __('Seen Recently'),
            self::Offline => __('Offline'),
            default => parent::getDescription((int) $value),
        };
    }
}
