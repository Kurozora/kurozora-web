<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

class MangaItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly string $originalTitle,
        readonly array $attributes,
        readonly ?string $synopsis,
        readonly ?string $imageURL,
        readonly array $relations,
        readonly array $studios,
        readonly array $genres,
        readonly array $authors,
    ) {}
}
