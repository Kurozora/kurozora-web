<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class PersonItem extends AbstractItem
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $imageURL,
        public readonly string $name,
        public readonly string $japaneseName,
        public readonly ?array $alternativeNames,
        public readonly ?string $about,
        public readonly ?string $birthday,
        public readonly array $websites,
        public readonly array $animeCharacters,
        public readonly array $animeStaff,
        public readonly array $mangas,
        public readonly array $marriages,
        public readonly ?string $deceasedDate,
    ) {}
}
