<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class PictureItem extends AbstractItem
{
    public function __construct(
        readonly string $type,
        readonly string $id,
        readonly array $imageURLs
    ) {}
}
