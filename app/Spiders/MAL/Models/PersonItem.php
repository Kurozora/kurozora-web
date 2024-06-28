<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class PersonItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly ?string $imageURL,
        readonly string $name,
        readonly string $japaneseName,
        readonly ?array $alternativeNames,
        readonly ?string $about,
        readonly ?string $birthday,
        readonly array $websites,
        readonly array $animeCharacters,
        readonly array $animeStaff,
        readonly array $mangas,
    ) {}
}
