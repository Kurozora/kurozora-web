<?php

namespace App\Traits\Spider;

use App\Spiders\MAL\Models\MangaCharacterItem;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

trait ParsesMangaCharacters
{
    /**
     * Parse the cast from a manga's characters page.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    protected function parseMangaCharacters(Response $response): Generator
    {
        $regex = '/manga\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/manga/')->value();

        if ($response->getStatus() >= 400) {
            logger()->error('Manga Character: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:MANGA:' . $id . '] Parsing character response');

        $cast = collect($response->filter('table[class*="manga-character-table"]')
            ->each(function (Crawler $item) {
                $regex = '/character\/(\d*)/';
                $characterID = str($item->filter('a[href*="/character/"]')->attr('href', ''))
                    ->match($regex)
                    ->remove('/character/')
                    ->value();
                // MAL lists the cast as "Family, Given".
                $characterName = str($item->filter('h3[class*="character_name"]')->text(''))
                    ->trim()
                    ->explode(', ', 2)
                    ->implode(' ');
                $castRole = str($item->filter('.spaceit_pad small')->text(''))
                    ->trim()
                    ->value();

                return [
                    'character' => [
                        'id' => $characterID,
                        'name' => $characterName,
                    ],
                    'cast_role' => $castRole,
                ];
            }))
            ->filter(fn (array $cast) => !empty($cast['character']['id']))
            ->values()
            ->all();

        yield $this->item(new MangaCharacterItem($id, $cast));
    }
}
