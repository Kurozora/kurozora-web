<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static MediaCollection Default()
 * @method static MediaCollection Banner()
 * @method static MediaCollection Logo()
 * @method static MediaCollection Poster()
 * @method static MediaCollection Profile()
 * @method static MediaCollection Screenshot()
 * @method static MediaCollection Symbol()
 */
final class MediaCollection extends Enum
{
    const Default = 'default';
    const Banner = 'banner';
    const Logo = 'logo';
    const Poster = 'poster';
    const Profile = 'profile';
    const Screenshot = 'screenshot';
    const Symbol = 'symbol';
}