<?php

namespace App\Processors\MAL;

use App\Enums\MediaCollection;
use App\Events\BareBonesAnimeAdded;
use App\Events\BareBonesMangaAdded;
use App\Events\BareBonesPersonAdded;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\Models\CharacterItem;
use DB;
use Exception;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;
use Throwable;

class CharacterProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            CharacterItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');

        logger()->channel('stderr')->info('🔄 [MAL_ID:CHARACTER:' . $malID . '] Processing ' . $malID);

        $character = Character::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        $imageURL = $item->get('imageURL');
        $name = $item->get('name');
        $japaneseName = $item->get('japaneseName');
        $alternativeNames = $this->getAlternativeNames($item->get('alternativeNames'), $character);
        $about = $this->getAbout($item->get('about'));
        $animes = $item->get('animes') ?? [];
        $mangas = $item->get('mangas') ?? [];
        $people = $item->get('people') ?? [];

        if (empty($character)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:CHARACTER:' . $malID . '] Creating character');

            $character = Character::withoutGlobalScopes()
                ->create([
                    'mal_id' => $malID,
                    'name' => $name,
                    'ja' => [
                        'name' => $japaneseName,
                        'about' => null
                    ],
                    'nicknames' => $alternativeNames,
                    'about' => $about
                ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $malID . '] Done creating character');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:CHARACTER:' . $malID . '] Updating attributes');
            $newAlternativeNames = array_values(array_unique(array_merge($character->nicknames?->toArray() ?? [], $alternativeNames ?? [])));

            $character->update([
                'mal_id' => $malID,
                'name' => $name,
                'ja' => [
                    'name' => $japaneseName,
                    'about' => null
                ],
                'nicknames' => $newAlternativeNames,
                'about' => $about,
            ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $malID . '] Done updating attributes');
        }

        // Add poster image
        $this->addProfileImage($imageURL, $character);

        // Add anime relations
        $this->addAnimes($animes, $character);

        // Add manga relations
        $this->addMangas($mangas, $character);

        // Add people relations
        $this->addPeople($people, $character);

        logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $malID . '] Done processing character');
        return $item;
    }

    /**
     * Get the alternative names.
     *
     * @param ?array         $alternativeNames
     * @param Character|null $character
     *
     * @return array|null
     */
    private function getAlternativeNames(?array $alternativeNames, ?Character $character): ?array
    {
        if (empty($alternativeNames)) {
            return null;
        }

        $currentAlternativeNames = $character?->alternative_names?->toArray() ?? [];
        $newAlternativeNames = empty(count($alternativeNames)) ? $currentAlternativeNames : array_merge($currentAlternativeNames, $alternativeNames);

        return count($newAlternativeNames) ? array_values(array_unique($newAlternativeNames)) : null;
    }

    /**
     * The 'about' string of the character.
     *
     * @param string|null $about
     *
     * @return ?string
     */
    private function getAbout(?string $about): ?string
    {
        $about = empty(trim($about)) ? null : $about;

        if (!empty($about)) {
            $about = preg_replace_array('/\(Source:[^ ]*|\)$/i', ['Source', ''], $about);
        }

        return $about;
    }

    /**
     * Add anime relations
     *
     * @param array     $animes
     * @param Character $character
     *
     * @return void
     */
    private function addAnimes(array $animes, Character $character): void
    {
        if (empty($animes)) {
            return;
        }

        $animesCollection = collect($animes);

        // Add missing anime
        try {
            $animeIDs = $animesCollection->pluck('id');
            $animes = Anime::withoutGlobalScopes()->whereIn('mal_id', $animeIDs->toArray());
            $missingAnimeIDs = $animeIDs->diff($animes->pluck('mal_id'));

            if ($missingAnimeIDs->isNotEmpty()) {
                logger()->channel('stderr')->info('↔️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Adding anime');

                DB::transaction(function () use ($animesCollection, $missingAnimeIDs) {
                    $missingAnimeIDs->each(function ($missingAnimeID) use ($animesCollection) {
                        $missingAnime = $animesCollection->firstWhere('id', '=', $missingAnimeID);

                        $anime = Anime::create([
                            'mal_id' => $missingAnime['id'],
                            'original_title' => $missingAnime['name']
                        ]);

                        event(new BareBonesAnimeAdded($anime));
                    });
                });

                logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Done adding anime staff');
            }
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:CHARACTER:' . $character->mal_id . '] Failed adding anime staff: ' . $e->getMessage());
        }
    }

    /**
     * Add manga relations
     *
     * @param array     $mangas
     * @param Character $character
     *
     * @return void
     */
    private function addMangas(array $mangas, Character $character): void
    {
        if (empty($mangas)) {
            return;
        }

        $mangasCollection = collect($mangas);

        // Add missing manga
        try {
            $mangaIDs = $mangasCollection->pluck('id');
            $mangas = Manga::withoutGlobalScopes()->whereIn('mal_id', $mangaIDs->toArray());
            $missingMangaIDs = $mangaIDs->diff($mangas->pluck('mal_id'));

            if ($missingMangaIDs->isNotEmpty()) {
                logger()->channel('stderr')->info('↔️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Adding manga');

                DB::transaction(function () use ($mangasCollection, $missingMangaIDs) {
                    $missingMangaIDs->each(function ($missingMangaID) use ($mangasCollection) {
                        $missingManga = $mangasCollection->firstWhere('id', '=', $missingMangaID);

                        $manga = Manga::create([
                            'mal_id' => $missingManga['id'],
                            'original_title' => $missingManga['name']
                        ]);

                        event(new BareBonesMangaAdded($manga));
                    });
                });

                logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Done adding manga staff');
            }
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:CHARACTER:' . $character->mal_id . '] Failed adding manga staff: ' . $e->getMessage());
        }
    }

    /**
     * Add people relations
     *
     * @param array     $people
     * @param Character $character
     *
     * @return void
     */
    private function addPeople(array $people, Character $character): void
    {
        if (empty($people)) {
            return;
        }

        $peopleCollection = collect($people);

        // Add missing manga
        try {
            $personIDs = $peopleCollection->pluck('id');
            $people = Person::withoutGlobalScopes()->whereIn('mal_id', $personIDs->toArray());
            $missingPersonIDs = $personIDs->diff($people->pluck('mal_id'));

            if ($missingPersonIDs->isNotEmpty()) {
                logger()->channel('stderr')->info('↔️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Adding person');

                DB::transaction(function () use ($peopleCollection, $missingPersonIDs) {
                    $missingPersonIDs->each(function ($missingPersonID) use ($peopleCollection) {
                        $missingPerson = $peopleCollection->firstWhere('id', '=', $missingPersonID);

                        $person = Person::create([
                            'mal_id' => $missingPerson['id'],
                            'name' => $this->getName($missingPerson['name'])
                        ]);

                        event(new BareBonesPersonAdded($person));
                    });
                });

                logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Done adding person');
            }
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:CHARACTER:' . $character->mal_id . '] Failed adding person: ' . $e->getMessage());
        }
    }

    /**
     * Get the name of the person.
     *
     * @param null|string $name
     *
     * @return array
     */
    private function getName(?string $name): array
    {
        if (empty($name)) {
            return [];
        }

        return array_reverse(explode(', ', $name));
    }

    /**
     * Download and link the given image to the specified character.
     *
     * @param string|null $imageUrl
     * @param Character   $character
     *
     * @return void
     */
    private function addProfileImage(?string $imageUrl, Character $character): void
    {
        if (!empty($imageUrl) && empty($character->getFirstMedia(MediaCollection::Profile))) {
            try {
                logger()->channel('stderr')->info('🌄 [MAL_ID:CHARACTER:' . $character->mal_id . '] Adding profile image');

                $character->updateImageMedia(MediaCollection::Profile(), $imageUrl, $character->name);

                logger()->channel('stderr')->info('✅️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Done adding profile image');
            } catch (Exception $e) {
                logger()->channel('stderr')->error('❌️ [MAL_ID:CHARACTER:' . $character->mal_id . '] Failed adding profile image: ' . $e->getMessage());
            }
        }
    }
}
