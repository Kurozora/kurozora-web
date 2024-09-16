<?php

namespace App\Processors\MAL;

use App\Spiders\MAL\ProducerSpider;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use RoachPHP\Support\Configurable;

class CompanyProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $producerURLs = collect($item->get('producerIDs'))
            ->map(function ($producerID) {
                return config('scraper.domains.mal.producer') . '/' . $producerID;
            })
            ->toArray();
        $magazineURLs = collect($item->get('magazineIDs'))
            ->map(function ($magazineID) {
                return config('scraper.domains.mal.magazine') . '/' . $magazineID;
            })
            ->toArray();

        if (!empty($producerURLs)) {
            logger()->channel('stderr')->debug('processing: ' . implode(', ', $producerURLs));
            Roach::startSpider(ProducerSpider::class, new Overrides(startUrls: $producerURLs));
        }

        if (!empty($magazineURLs)) {
            dd('----- Caught magazines', $magazineURLs);
//            Roach::startSpider(ProducerSpider::class, new Overrides(startUrls: $magazineURLs));
        }

        return $item;
    }
}
