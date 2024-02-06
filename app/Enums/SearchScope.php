<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchScope Kurozora()
 * @method static SearchScope Library()
 */
final class SearchScope extends Enum
{
    const string Kurozora = 'kurozora';
    const string Library = 'library';
}
