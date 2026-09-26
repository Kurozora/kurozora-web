<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class VideoItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly array $videos
    ) {}
}
