<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

class MangaStatItem  extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly array $scores,
        readonly float $scoreAverage,
        readonly int $scoreCount,
    ) {}
}
