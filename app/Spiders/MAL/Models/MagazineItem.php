<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class MagazineItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly string $page,
        readonly string $name,
        readonly array $mangas
    ) {}
}
