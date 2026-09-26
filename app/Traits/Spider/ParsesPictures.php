<?php

namespace App\Traits\Spider;

use App\Spiders\MAL\Models\PictureItem;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

trait ParsesPictures
{
    /**
     * Parse the image gallery from an entity's pictures page.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parsePictures(Response $response): Generator
    {
        $uri = str($response->getUri());
        $type = match (true) {
            $uri->contains('/anime/') => 'anime',
            $uri->contains('/manga/') => 'manga',
            $uri->contains('/character/') => 'character',
            $uri->contains('/people/') => 'people',
            default => null,
        };
        preg_match('#/(?:anime|manga|character|people)/(\d+)#', $uri->value(), $matches);
        $id = $matches[1] ?? null;

        if (empty($type) || empty($id)) {
            return $this->item([]);
        }

        if ($response->getStatus() >= 400) {
            logger()->error('Pictures: ' . $type . ':' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:' . strtoupper($type) . ':' . $id . '] Parsing pictures response');

        $imageURLs = collect($response->filter('.picSurround a[href^="https://cdn.myanimelist.net/images/"]')
            ->each(function (Crawler $item) {
                return str($item->attr('href', ''))
                    ->trim()
                    ->value();
            }))
            ->filter()
            ->reject(function (string $url) {
                return str($url)->contains(['questionmark', 'qm_50', 'na.gif']);
            })
            ->unique()
            ->values()
            ->all();

        yield $this->item(new PictureItem($type, $id, $imageURLs));
    }
}
