<?php

namespace App\Traits\Spider;

use App\Spiders\MAL\Models\AnimeCharacterItem;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

trait ParsesAnimeCharacters
{
    /**
     * Parse the cast and staff from an anime's characters page.
     *
     * @param Response $response
     *
     * @return Generator<ParseResult>
     */
    protected function parseAnimeCharacters(Response $response): Generator
    {
        $regex = '/anime\/(\d*)/';
        $uri = str($response->getUri());
        $id = $uri->match($regex)->remove('/anime/')->value();

        if ($response->getStatus() >= 400) {
            logger()->error('Anime Character: ' . $id . ';status:' . $response->getStatus());
            return $this->item([]);
        }

        logger()->channel('stderr')->debug('🕷 [MAL_ID:ANIME:' . $id . '] Parsing character response');

        $cast = collect($response->filter('table[class*="anime-character-table"]')
            ->each(function (Crawler $item) {
                $regex = '/character\/(\d*)/';
                $characterID = str($item->filter('a[href^="https://myanimelist.net/character"]')->attr('href', ''))
                    ->match($regex)
                    ->remove('/character/')
                    ->value();
                $characterData = $item->filter('td')
                    ->eq(1)
                    ->children('.spaceit_pad');
                // MAL lists the cast as "Family, Given".
                $characterName = str($characterData->eq(0)->text(''))
                    ->trim()
                    ->explode(', ', 2)
                    ->implode(' ');
                $castRole = str($characterData->eq(1)->text(''))
                    ->trim()
                    ->value();

                $actors = collect($item->filter('td')
                    ->eq(2)
                    ->filter('table tr')
                    ->each(function (Crawler $item) {
                        $regex = '/people\/(\d*)/';
                        $personID = str($item->filter('a[href^="https://myanimelist.net/people"]')->attr('href', ''))
                            ->match($regex)
                            ->remove('/people/')
                            ->value();
                        $personName = str($item->filter('a[href^="https://myanimelist.net/people"]')->text(''))
                            ->trim()
                            ->value();
                        $language = str($item->filter('[class*="character-language"]')->text(''))
                            ->trim()
                            ->value();

                        return [
                            'id' => $personID,
                            'name' => $personName,
                            'language' => $language,
                        ];
                    }))
                    ->filter(fn (array $actor) => !empty($actor['id']))
                    ->values()
                    ->all();

                return [
                    'character' => [
                        'id' => $characterID,
                        'name' => $characterName,
                    ],
                    'cast_role' => $castRole,
                    'actors' => $actors
                ];
            }))
            ->filter(fn (array $entry) => !empty($entry['character']['id']))
            ->values()
            ->all();

        $staff = collect($response->filter('h2:contains("Staff")')
            ->ancestors()
            ->nextAll()
            ->each(function (Crawler $item) {
                $staffData = $item->filter('td')
                    ->eq(1);
                $regex = '/people\/(\d*)/';
                $staffID = str($staffData->filter('a[href^="https://myanimelist.net/people"]')->attr('href', ''))
                    ->match($regex)
                    ->remove('/people/')
                    ->value();
                $staffName = str($staffData->filter('a[href^="https://myanimelist.net/people"]')->text(''))
                    ->trim()
                    ->value();
                $staffRole = str($staffData->filter('small')->text(''))
                    ->trim()
                    ->value();

                return [
                    'id' => $staffID,
                    'name' => $staffName,
                    'role' => $staffRole,
                ];
            }))
            ->filter(fn (array $member) => !empty($member['id']))
            ->values()
            ->all();

        yield $this->item(new AnimeCharacterItem($id, $cast, $staff));
    }
}
