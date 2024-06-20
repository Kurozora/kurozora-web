<?php

namespace App\Processors\MAL;

use App\Models\Anime;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\StaffRole;
use App\Spiders\MAL\Models\AnimeCharacterItem;
use DB;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

final class AnimeCharacterProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            AnimeCharacterItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        logger()->channel('stderr')->info('🔄 [MAL_ID:ANIME:' . $malID . '] Processing characters');

        $anime = Anime::with(['cast', 'mediaStaff'])
            ->withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);
        $cast = $anime->cast;
        $mediaStaff = $anime->mediaStaff;
        $characters = collect($item->get('characters'));
        $staff = collect($item->get('staff'));

//        dd([
//            'id' => $id,
//            'characters' => $characters,
//            'staff' => $staff
//        ]);
//
//        foreach ($characters as $character) {
//            logger()->channel('stderr')->info('🛠 [MAL_ID:ANIME:' . $malID . '] Updating characters');
//
//            $cast->updateOrInsert([
//                'model_type' => $anime->getMorphClass(),
//                'model_id' => $anime->id,
//            ], []);
//
//            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done updating characters');
//        }

        foreach ($staff->chunk(100) as $staffChunk) {
            logger()->channel('stderr')->info('🛠 [MAL_ID:ANIME:' . $malID . '] Updating staff');
            $ids = $staffChunk->pluck('id');
            $people = Person::whereIn('mal_id', $ids->toArray())
                ->get();

            if ($people->count() !== $ids->count()) {
                logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect staff count');
                $missingIDs = $ids->diff($people->pluck('mal_id'));

                DB::transaction(function () use ($staffChunk, $people, $missingIDs) {
                    $missingIDs->each(function ($missingID) use ($staffChunk, $people) {
                        $staff = $staffChunk->firstWhere('id', '=', $missingID);
                        $name = explode(', ', $staff['name']); // Lastname, Firstname

                        $person = Person::create([
                            'mal_id' => $missingID,
                            'first_name' => array_key_exists(1, $name) ? $name[1] : $name[0] ?? null,
                            'last_name' => array_key_exists(1, $name) ? $name[0] ?? null : null,
                        ]);

                        $people->add($person);
                    });
                });
            }

            $staffChunk->each(function ($staff) use ($malID, $anime, $people, $mediaStaff) {
                $id = $staff['id'];
                $person = $people->firstWhere('mal_id', '=', $id);

                $roles = collect(explode(', ', $staff['role']));
                $staffRoles = StaffRole::whereIn('name', $roles->toArray())
                    ->get();

                if ($staffRoles->count() !== $roles->count()) {
                    logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect roles count');

                    $missingRoles = $roles->diff($staffRoles->pluck('name'));

                    dd($missingRoles);
//                    DB::transaction(function () use ($staffRoles, $missingRoles) {
//                        $missingRoles->each(function ($missingRole) use ($staffRoles) {
//                            $staffRole = StaffRole::create([
//                                'name' => $missingRole
//                            ]);
//
//                            $staffRoles->add($staffRole);
//                        });
//                    });
                }

                $staffRoles->each(function (StaffRole $role) use ($anime, $person, $mediaStaff) {
                    $newMediaStaff = MediaStaff::firstOrCreate([
                        'model_type' => $anime->getMorphClass(),
                        'model_id' => $anime->id,
                        'person_id' => $person->id,
                        'staff_role_id' => $role->id,
                    ]);

                    if ($newMediaStaff->wasRecentlyCreated) {
                        $mediaStaff->add($newMediaStaff);
                    }
                });
            });

            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done updating staff');
        }
        dd($mediaStaff->count(), $staff->count());

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done processing characters');
        return $item;
    }
}
