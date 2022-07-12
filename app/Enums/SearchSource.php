<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchSource Kurozora()
 * @method static SearchSource Google()
 */
final class SearchSource extends Enum
{
    const Kurozora = 'gs_kurozora';
    const Google = 'mc_google';
}
