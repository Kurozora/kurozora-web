<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class ProducerItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly ?string $imageURL,
        readonly string $name,
        readonly ?string $japaneseName,
        readonly array $alternativeNames,
        readonly ?string $about,
        readonly ?string $foundedAt,
        readonly ?string $defunctAt,
        readonly array $socials,
        readonly array $websites
    ) {}
}
