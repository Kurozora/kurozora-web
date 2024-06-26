<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class PersonItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly ?string $imageUrl,
        readonly string $name,
        readonly string $japaneseName,
        readonly array $alternativeNames,
        readonly ?string $about,
        readonly ?string $birthday,
        readonly ?string $website,
        readonly array $animes,
        readonly array $mangas,
        readonly array $staff,
    ) {}
}
