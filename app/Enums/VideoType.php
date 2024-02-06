<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static VideoType Default()
 * @method static VideoType CommercialMessage()
 * @method static VideoType PromotionalVideo()
 * @method static VideoType Teaser()
 * @method static VideoType Trailer()
 */
final class VideoType extends Enum
{
    const int Default           = 0;
    const int CommercialMessage = 1;
    const int PromotionalVideo  = 2;
    const int Teaser            = 3;
    const int Trailer           = 4;
}
