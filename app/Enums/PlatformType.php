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
    const Console = 1;
    const Arcade = 2;
    const Platform = 3;
    const OperatingSystem = 4;
    const PortableConsole = 5;
    const Computer = 6;
}
