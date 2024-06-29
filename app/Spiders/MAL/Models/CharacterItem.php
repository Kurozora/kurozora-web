<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class CharacterItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly ?string $imageURL,
        readonly string $name,
        readonly string $japaneseName,
        readonly ?array $alternativeNames,
        readonly ?string $about,
        readonly array $animes,
        readonly array $mangas,
        readonly array $people,
    ) {}
}
