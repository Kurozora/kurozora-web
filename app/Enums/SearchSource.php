<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static SearchSource Kurozora()
 * @method static SearchSource Google()
 * @method static SearchSource OpenSearch()
 */
final class SearchSource extends Enum
{
    const string Kurozora = 'gs_kurozora';
    const string Google = 'mc_google';
    const string OpenSearch = 'opensearch';
}
