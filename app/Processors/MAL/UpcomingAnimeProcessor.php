<?php

namespace App\Processors\MAL;

use App\Spiders\MAL\AnimeSpider;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use RoachPHP\Support\Configurable;

class UpcomingAnimeProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $ids = $item->all();

        $urls = [];
        foreach ($ids as $id) {
            $urls[] = config('scraper.domains.mal.anime') . '/' . $id;
        }

        Roach::startSpider(AnimeSpider::class, new Overrides(startUrls: $urls));

        return $item;
    }
}
