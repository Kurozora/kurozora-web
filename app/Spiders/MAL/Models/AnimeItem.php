<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class AnimeItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly string $originalTitle,
        readonly array $attributes,
        readonly string $synopsis,
        readonly ?string $imageUrl,
        readonly ?string $videoUrl,
        readonly array $studios,
        readonly array $genres,
        readonly array $openings,
        readonly array $ending,
    ) {}
}
