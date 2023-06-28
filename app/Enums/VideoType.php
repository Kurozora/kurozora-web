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
    const Default           = 0;
    const CommercialMessage = 1;
    const PromotionalVideo  = 2;
    const Teaser            = 3;
    const Trailer           = 4;
}
