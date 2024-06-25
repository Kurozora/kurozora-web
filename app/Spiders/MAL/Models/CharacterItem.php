<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class CharacterItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly string $name,
        readonly string $japaneseName,
        readonly string $alternativeNames,
        readonly ?string $synopsis,
        readonly ?string $imageUrl,
        readonly array $animes,
        readonly array $mangas,
        readonly array $voiceActors,
    ) {}
}
