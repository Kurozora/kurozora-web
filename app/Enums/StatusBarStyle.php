<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static StatusBarStyle Default()
 * @method static StatusBarStyle LightContent()
 */
final class StatusBarStyle extends Enum
{
    const Default = 0;
    const LightContent = 1;

    /**
     * The string value of the status bar style type.
     *
     * @return string
     */
    public function stringValue(): string
    {
        return match($this->value) {
            self::LightContent => 'LightContent',
            default => 'Default',
        };
    }
}
