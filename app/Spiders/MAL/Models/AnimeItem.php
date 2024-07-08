<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class AnimeItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly string $originalTitle,
        readonly array $attributes,
        readonly ?string $synopsis,
        readonly ?string $imageURL,
        readonly ?string $videoURL,
        readonly array $studios,
        readonly array $genres,
        readonly array $relations,
        readonly array $openings,
        readonly array $endings,
    ) {}
}
