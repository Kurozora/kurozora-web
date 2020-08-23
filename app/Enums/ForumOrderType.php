<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ForumOrderType Best()
 * @method static ForumOrderType Top()
 * @method static ForumOrderType New()
 * @method static ForumOrderType Old()
 * @method static ForumOrderType Poor()
 * @method static ForumOrderType Controversial()
 */
final class ForumOrderType extends Enum
{
    const Best = "best";
    const Top = "top";
    const New = "new";
    const Old = "old";
    const Poor = "poor";
    const Controversial = "controversial";
}
