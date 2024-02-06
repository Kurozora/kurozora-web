<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static UserActivityStatus Online()
 * @method static UserActivityStatus SeenRecently()
 * @method static UserActivityStatus Offline()
 */
final class UserActivityStatus extends Enum
{
    const int Online = 0;
    const int SeenRecently = 1;
    const int Offline = 2;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription(mixed $value): string
    {
        return match ((int) $value) {
            self::Online => __('Online'),
            self::SeenRecently => __('Seen Recently'),
            self::Offline => __('Offline'),
            default => parent::getDescription((int) $value),
        };
    }
}
