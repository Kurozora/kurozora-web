<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PlayerPerspective FirstPerson()
 * @method static PlayerPerspective ThirdPerson()
 * @method static PlayerPerspective BirdView()
 * @method static PlayerPerspective SideView()
 * @method static PlayerPerspective Text()
 * @method static PlayerPerspective Auditory()
 * @method static PlayerPerspective VirtualReality()
 */
final class PlayerPerspective extends Enum
{
    const int FirstPerson =     1;
    const int ThirdPerson =     2;
    const int BirdView =        3;
    const int SideView =        4;
    const int Text =            5;
    const int Auditory =        6;
    const int VirtualReality =  7;
}
