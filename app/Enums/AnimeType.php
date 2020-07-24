<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AnimeType extends Enum
{
    const Unknown   = 0;
    const TV        = 1;
    const Movie     = 2;
    const Music     = 3;
    const ONA       = 4;
    const OVA       = 5;
    const Special   = 6;
}
