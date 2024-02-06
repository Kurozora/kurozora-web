<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PlatformType Console()
 * @method static PlatformType Arcade()
 * @method static PlatformType Platform()
 * @method static PlatformType OperatingSystem()
 * @method static PlatformType PortableConsole()
 * @method static PlatformType Computer()
 */
final class PlatformType extends Enum
{
    const int Console = 1;
    const int Arcade = 2;
    const int Platform = 3;
    const int OperatingSystem = 4;
    const int PortableConsole = 5;
    const int Computer = 6;
}
