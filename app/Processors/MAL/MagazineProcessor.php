<?php

namespace App\Processors\MAL;

use App\Enums\StudioType;
use App\Events\BareBonesMangaAdded;
use App\Models\Manga;
use App\Models\Studio;
use App\Spiders\MAL\Models\MagazineItem;
use DB;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;
use Throwable;

class MagazineProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            MagazineItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        $page = $item->get('page');

        logger()->channel('stderr')->info('🔄 [MAL_ID:MAGAZINE:' . $malID . '] Processing ' . $malID . ' page ' . $page);

        $studio = Studio::withoutGlobalScopes()
            ->where([
                ['mal_id', '=', $malID],
                ['type', '=', StudioType::Manga],
            ])
            ->first();

        $name = $item->get('name');
        $mangas = $item->get('mangas') ?? [];

        if (empty($studio)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:MAGAZINE:' . $malID . '] Creating magazine');

            Studio::withoutGlobalScopes()
                ->create([
                    'mal_id' => $malID,
                    'type' => StudioType::Manga,
                    'name' => $name,
                ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:MAGAZINE:' . $malID . '] Done creating magazine');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:MAGAZINE:' . $malID . '] Updating attributes');
            $newName = empty($name) ? $studio->name : $name;

            $studio->update([
                'mal_id' => $malID,
                'name' => $newName,
            ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:MAGAZINE:' . $malID . '] Done updating attributes');
        }

        // Add mangas
        try {
            logger()->channel('stderr')->info('↔️ [MAL_ID:MAGAZINE:' . $malID . '] Adding mangas');
            $this->addMangas($mangas);
            logger()->channel('stderr')->info('✅️ [MAL_ID:MAGAZINE:' . $malID . '] Done adding mangas');
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:MAGAZINE:' . $studio->mal_id . '] Failed adding mangas: ' . $e->getMessage());
        }

        logger()->channel('stderr')->info('✅️ [MAL_ID:MAGAZINE:' . $malID . '] Done processing magazine');
        return $item;
    }

    /**
     * Add studio's missing mangas.
     *
     * @param array $mangas
     *
     * @return void
     * @throws Throwable
     */
    private function addMangas(array $mangas): void
    {
        $mangas = collect($mangas);
        $malIDs = $mangas->pluck('id');
        $mangasInDatabase = Manga::withoutGlobalScopes()
            ->whereIn('mal_id', $malIDs->toArray())
            ->pluck('mal_id');
        $missingMangas = $malIDs->diff($mangasInDatabase);

        if ($missingMangas->count()) {
            DB::transaction(function () use ($mangas, $missingMangas) {
                $missingMangas->each(function ($missingManga) use ($mangas) {
                    $missingManga = $mangas->firstWhere('id', '=', $missingManga);

                    $manga = Manga::create([
                        'mal_id' => $missingManga['id'],
                        'original_title' => $missingManga['title'],
                    ]);

                    event(new BareBonesMangaAdded($manga));
                });
            });
        }
    }
}
