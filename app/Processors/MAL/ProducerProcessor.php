<?php

namespace App\Processors\MAL;

use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Models\Studio;
use App\Spiders\MAL\Models\ProducerItem;
use Carbon\Carbon;
use Exception;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

class ProducerProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            ProducerItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');

        logger()->channel('stderr')->info('🔄 [MAL_ID:PRODUCER:' . $malID . '] Processing ' . $malID);

        $studio = Studio::withoutGlobalScopes()
            ->where([
                ['mal_id', '=', $malID],
                ['type', '=', StudioType::Anime],
            ])
            ->first();

        $imageURL = $item->get('imageURL');
        $name = $item->get('name');
        $japaneseName = $item->get('japaneseName');
        $alternativeNames = $this->getAlternativeNames($item->get('alternativeNames'), $studio);
        $about = $this->getAbout($item->get('about'));
        $foundedAt = $this->getDate($item->get('foundedAt'));
        $defunctAt = $this->getDate($item->get('defunctAt'));
        $socials = $item->get('socials') ?? [];
        $websites = $item->get('websites') ?? [];

        if (empty($studio)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:PRODUCER:' . $malID . '] Creating producer');

            $studio = Studio::withoutGlobalScopes()
                ->create([
                    'mal_id' => $malID,
                    'type' => StudioType::Anime,
                    'name' => $name,
                    'japanese_name' => $japaneseName,
                    'alternative_names' => $alternativeNames,
                    'about' => $about,
                    'social_urls' => $socials,
                    'website_urls' => $websites,
                    'founded_at' => $foundedAt?->toDateString(),
                    'defunct_at' => $defunctAt?->toDateString(),
                ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:PRODUCER:' . $malID . '] Done creating producer');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:PRODUCER:' . $malID . '] Updating attributes');
            $newName = empty($name) ? $studio->name : $name;
            $newJapaneseName = empty($japaneseName) ? $studio->japanese_name : $japaneseName;
            $newAlternativeNames = array_values(array_unique(array_merge($studio->alternative_names?->toArray() ?? [], $alternativeNames ?? [])));
            $newAbout = empty($about) ? $studio->about : $about;
            $newSocials = $this->getLinks($studio->social_urls?->toArray(), $socials);
            $newWebsites = $this->getLinks($studio->website_urls?->toArray(), $websites);
            $newFoundedAt = empty($foundedAt) ? $studio->founded_at : $foundedAt;
            $newDefunctAt = empty($defunctAt) ? $studio->defunct_at : $defunctAt;

            $studio->update([
                'mal_id' => $malID,
                'name' => $newName,
                'japanese_name' => $newJapaneseName,
                'alternative_names' => empty($newAlternativeNames) ? null : $newAlternativeNames,
                'about' => $newAbout,
                'social_urls' => $newSocials,
                'website_urls' => $newWebsites,
                'founded_at' => $newFoundedAt?->toDateString(),
                'defunct_at' => $newDefunctAt?->toDateString(),
            ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:PRODUCER:' . $malID . '] Done updating attributes');
        }

        // Add poster image
        $this->addProfileImage($imageURL, $studio);

        logger()->channel('stderr')->info('✅️ [MAL_ID:PRODUCER:' . $malID . '] Done processing producer');
        return $item;
    }

    /**
     * Get the alternative names.
     *
     * @param ?array      $alternativeNames
     * @param Studio|null $studio
     *
     * @return array|null
     */
    private function getAlternativeNames(?array $alternativeNames, ?Studio $studio): ?array
    {
        if (empty($alternativeNames)) {
            return null;
        }

        $currentAlternativeNames = $studio?->alternative_names?->toArray() ?? [];
        $newAlternativeNames = empty(count($alternativeNames)) ? $currentAlternativeNames : array_merge($currentAlternativeNames, $alternativeNames);

        return count($newAlternativeNames) ? array_values(array_unique($newAlternativeNames)) : null;
    }

    /**
     * Gt the websites of the studio.
     *
     * @param null|array $currentLinks
     * @param null|array $newLinks
     *
     * @return array
     */
    private function getLinks(?array $currentLinks, ?array $newLinks): array
    {
        return collect($newLinks ?? [])
            ->merge($currentLinks ?? [])
            ->transform(function ($website) {
                return str($website)
                    ->trim()
                    ->replaceEnd('/', '')
                    ->value();
            })
            ->unique()
            ->toArray();
    }

    /**
     * The 'about' string of the studio.
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
     * Get a Carbon object from a studio date string.
     *
     * @param string|null $date
     *
     * @return ?Carbon
     */
    private function getDate(?string $date): ?Carbon
    {
        $str = empty(trim($date)) ? null : $date;

        try {
            $date = Carbon::createFromFormat('M d, Y', $str);
            if ($date) {
                return $date;
            }
        } catch (Exception $exception) {
            try {
                $date = Carbon::createFromFormat('M Y', $str);
                if ($date) {
                    $date->day(1);
                    return $date;
                }
            } catch (Exception $exception) {
                try {
                    $date = Carbon::createFromFormat('Y', $str);
                    if ($date) {
                        $date->month(1)
                            ->day(1);
                        return $date;
                    }
                } catch (Exception $exception) {
                }
            }
        }

        return null;
    }

    /**
     * Download and link the given image to the specified studio.
     *
     * @param string|null $imageUrl
     * @param Studio      $studio
     *
     * @return void
     */
    private function addProfileImage(?string $imageUrl, Studio $studio): void
    {
        if (!empty($imageUrl) && empty($studio->getFirstMedia(MediaCollection::Profile))) {
            try {
                logger()->channel('stderr')->info('🌄 [MAL_ID:PRODUCER:' . $studio->mal_id . '] Adding profile image');

                $studio->updateImageMedia(MediaCollection::Profile(), $imageUrl, $studio->name);

                logger()->channel('stderr')->info('✅️ [MAL_ID:PRODUCER:' . $studio->mal_id . '] Done adding profile image');
            } catch (Exception $e) {
                logger()->channel('stderr')->error('❌️ [MAL_ID:PRODUCER:' . $studio->mal_id . '] Failed adding profile image: ' . $e->getMessage());
            }
        }
    }
}
