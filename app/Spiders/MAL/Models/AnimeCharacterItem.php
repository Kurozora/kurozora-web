<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class AnimeCharacterItem extends AbstractItem
{
    public function __construct(
        readonly string $id,
        readonly array $cast,
        readonly array $staff
    ) {}
}
