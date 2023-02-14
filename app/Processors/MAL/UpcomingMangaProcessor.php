<?php

namespace App\Processors\MAL;

use App\Spiders\MAL\MangaSpider;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use RoachPHP\Support\Configurable;

class UpcomingMangaProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $ids = $item->all();

        $urls = [];
        foreach ($ids as $id) {
            $urls[] = config('scraper.domains.mal.manga') . '/' . $id;
        }

        Roach::startSpider(MangaSpider::class, new Overrides(startUrls: $urls));

        return $item;
    }
}
