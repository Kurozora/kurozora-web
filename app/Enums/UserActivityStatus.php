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

    public static function getDescription($value): string
    {
        switch($value) {
            case self::Online:
                return 'Online';
            case self::SeenRecently:
                return 'Seen Recently';
            case self::Offline:
                return 'Offline';
        }

        return parent::getDescription($value);
    }
}
