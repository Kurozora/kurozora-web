<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static LinkPreviewType LINK()
 * @method static LinkPreviewType IMAGE()
 * @method static LinkPreviewType VIDEO()
 * @method static LinkPreviewType AUDIO()
 */
final class LinkPreviewType extends Enum
{
    const int LINK = 0;
    const int IMAGE = 1;
    const int VIDEO = 2;
    const int AUDIO = 3;

    public static function parseDatabase(mixed $value): int
    {
        return (int) $value;
    }
}
