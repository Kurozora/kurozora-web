<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static LanguageSupportType Audio()
 * @method static LanguageSupportType Subtitles()
 * @method static LanguageSupportType Interface()
 */
final class LanguageSupportType extends Enum
{
    const int Audio =       1;
    const int Subtitles =   2;
    const int Interface =   3;
}
