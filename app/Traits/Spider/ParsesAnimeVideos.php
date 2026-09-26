<?php

namespace App\Traits\Spider;

use App\Spiders\MAL\Models\VideoItem;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

trait ParsesAnimeVideos
{
    /**
     * Parse the promotional videos from an anime's video page.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    public function parseVideos(Response $response): Generator
    {
        $regex = '/anime\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/anime/')->value();

        if ($response->getStatus() >= 400) {
            logger()->error('Anime Video: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:ANIME:' . $id . '] Parsing video response');

        // Promotional/music videos only; the episode-video block is intentionally excluded.
        $videos = collect($response->filter('.video-block:not(.episode-video) a.js-fancybox-video')
            ->each(function (Crawler $item) {
                $code = str($item->attr('href', ''))
                    ->match('#/embed/([\w-]{6,})#')
                    ->value();
                $title = str($item->filter('.info-container .title')->text(''))
                    ->trim()
                    ->value();

                return [
                    'code' => $code,
                    'title' => $title,
                ];
            }))
            ->filter(fn (array $video) => !empty($video['code']))
            ->unique('code')
            ->values()
            ->all();

        yield $this->item(new VideoItem($id, $videos));
    }
}
