<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static MediaCollection Default()
 * @method static MediaCollection Artwork()
 * @method static MediaCollection Banner()
 * @method static MediaCollection Logo()
 * @method static MediaCollection Poster()
 * @method static MediaCollection Profile()
 * @method static MediaCollection Screenshot()
 * @method static MediaCollection Symbol()
 */
final class MediaCollection extends Enum
{
    const string Default = 'default';
    const string Artwork = 'artwork';
    const string Banner = 'banner';
    const string Logo = 'logo';
    const string Poster = 'poster';
    const string Profile = 'profile';
    const string Screenshot = 'screenshot';
    const string Symbol = 'symbol';
}
