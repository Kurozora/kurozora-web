<?php

namespace App\Spiders\MAL\Models;

use RoachPHP\ItemPipeline\AbstractItem;

final class CompanyItem extends AbstractItem
{
    public function __construct(
        readonly array $producerIDs,
        readonly array $magazineIDs,
    ) {}
}
