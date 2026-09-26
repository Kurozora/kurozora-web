<?php

namespace App\Processors\MAL;

use App\Events\BareBonesCharacterAdded;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Manga;
use App\Models\MangaCast;
use App\Spiders\MAL\Models\MangaCharacterItem;
use DB;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

final class MangaCharacterProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            MangaCharacterItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        logger()->channel('stderr')->info('🔄 [MAL_ID:MANGA:' . $malID . '] Processing characters');

        $manga = Manga::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        if (empty($manga)) {
            logger()->channel('stderr')->error('❌ [MAL_ID:MANGA:' . $malID . '] Missing manga; skipping characters.');
            return $item;
        }

        $cast = collect($item->get('cast'));

        foreach ($cast->chunk(100) as $castChunk) {
            logger()->channel('stderr')->debug('🛠 [MAL_ID:MANGA:' . $malID . '] Updating characters');

            $characterIDs = $castChunk->pluck('character.id');
            $characters = Character::withoutGlobalScopes()
                ->with(['mediaStat', 'translations'])
                ->whereIn('mal_id', $characterIDs->toArray())
                ->get();

            // Rename characters to their cast names
            $castNames = $castChunk->pluck('character.name', 'character.id');
            $characters->each(function (Character $character) use ($castNames) {
                $castName = $castNames->get($character->mal_id);

                if (!empty($castName) && $character->name !== $castName) {
                    $character->update(['name' => $castName]);
                }
            });

            // Add missing characters
            if ($characters->count() !== $characterIDs->count()) {
                $missingIDs = $characterIDs->diff($characters->pluck('mal_id'));

                if ($missingIDs->isNotEmpty()) {
                    DB::transaction(function () use ($castChunk, $characters, $missingIDs) {
                        $missingIDs->each(function ($missingID) use ($castChunk, $characters) {
                            $characterCast = $castChunk->firstWhere('character.id', '=', $missingID);

                            $character = Character::create([
                                'mal_id' => $missingID,
                                'name' => $characterCast['character']['name'],
                                'ja' => [
                                    'name' => $characterCast['character']['name'],
                                ]
                            ]);

                            $characters->add($character);

                            event(new BareBonesCharacterAdded($character));
                        });
                    });
                }
            }

            // Add missing cast roles
            $roles = $castChunk->pluck('cast_role')->unique()->transform(function ($role) {
                return match ($role) {
                    'Main' => 'Protagonist',
                    'Supporting' => 'Supporting Character',
                    default => $role
                };
            });
            $castRoles = CastRole::whereIn('name', $roles->toArray())
                ->get();

            if ($castRoles->count() !== $roles->count()) {
                $missingRoles = $roles->diff($castRoles->pluck('name'));

                if ($missingRoles->isNotEmpty()) {
                    DB::transaction(function () use ($castRoles, $missingRoles) {
                        $missingRoles->each(function ($missingRole) use ($castRoles) {
                            $castRole = CastRole::firstOrCreate([
                                'name' => $missingRole
                            ], [
                                'description' => ''
                            ]);

                            $castRoles->add($castRole);
                        });
                    });
                }
            }

            $castChunk->each(function ($newCast) use ($manga, $characters, $castRoles) {
                $character = $characters->firstWhere('mal_id', '=', $newCast['character']['id']);

                if (empty($character)) {
                    return;
                }

                $castRoleName = match ($newCast['cast_role']) {
                    'Main' => 'Protagonist',
                    'Supporting' => 'Supporting Character',
                    default => $newCast['cast_role']
                };
                $castRole = $castRoles->firstWhere('name', '=', $castRoleName);

                if (empty($castRole)) {
                    return;
                }

                MangaCast::firstOrCreate([
                    'manga_id' => $manga->id,
                    'character_id' => $character->id,
                    'cast_role_id' => $castRole->id,
                ]);
            });

            logger()->channel('stderr')->debug('✅️ [MAL_ID:MANGA:' . $malID . '] Done updating characters');
        }

        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done processing characters');
        return $item;
    }
}
