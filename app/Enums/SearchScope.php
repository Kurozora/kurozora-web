<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchScope Kurozora()
 * @method static SearchScope Library()
 */
final class SearchScope extends Enum
{
    const Kurozora = 'kurozora';
    const Library = 'library';
}
