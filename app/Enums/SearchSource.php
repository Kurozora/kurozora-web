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
    const Kurozora = 'gs_kurozora';
    const Google = 'mc_google';
    const OpenSearch = 'opensearch';
}
