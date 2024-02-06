<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static VisualEffectViewStyle Light()
 * @method static VisualEffectViewStyle Dark()
 */
final class VisualEffectViewStyle extends Enum
{
    const int Light = 0;
    const int Dark = 1;

    /**
     * The string value of the visual effect view style type.
     *
     * @return string
     */
    public function stringValue(): string
    {
        return match($this->value) {
            self::Light => 'Light',
            default => 'Dark',
        };
    }
}
