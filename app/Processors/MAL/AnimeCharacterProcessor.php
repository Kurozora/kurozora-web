<?php

namespace App\Processors\MAL;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Language;
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
        $animeCast = $anime->cast;
        $mediaStaff = $anime->mediaStaff;
        $cast = collect($item->get('cast'));
        $staff = collect($item->get('staff'));

//        dd([
//            'id' => $id,
//            'cast' => $cast,
//            'staff' => $staff
//        ]);

        foreach ($cast->chunk(100) as $castChunk) {
            logger()->channel('stderr')->info('🛠 [MAL_ID:ANIME:' . $malID . '] Updating characters');

            $characterIDs = $castChunk->pluck('character.id');
            $characters = Character::whereIn('mal_id', $characterIDs->toArray())
                ->get();
            if ($characters->count() !== $characterIDs->count()) {
                $missingIDs = $characterIDs->diff($characters->pluck('mal_id'));

                if ($missingIDs->isNotEmpty()) {
                    logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect character count');

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
                        });
                    });
                }
            }

            $actors = $castChunk->pluck('actors.*')->collapse();
            $actorIDs = $actors->pluck('id');
            $people = Person::whereIn('mal_id', $actorIDs->toArray())
                ->get(['id', 'mal_id']);

            if ($people->count() !== $actorIDs->count()) {
                $missingIDs = $actorIDs->diff($people->pluck('mal_id'))->unique();

                if ($missingIDs->isNotEmpty()) {
                    logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect people count');

                    DB::transaction(function () use ($actors, $people, $missingIDs) {
                        $missingIDs->each(function ($missingID) use ($actors, $people) {
                            $voiceActor = $actors->firstWhere('id', '=', $missingID);
                            $name = explode(', ', $voiceActor['name']); // Lastname, Firstname
                            $firstName = array_key_exists(1, $name)
                                ? trim($name[1])
                                : (empty($name[0] ?? null)
                                    ? null
                                    : trim($name[0])
                                );
                            $lastName = array_key_exists(1, $name)
                                ? (empty($name[0] ?? null)
                                    ? null
                                    : trim($name[0])
                                )
                                : null;

                            $person = Person::create([
                                'mal_id' => $missingID,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                            ]);

                            $people->add($person);
                        });
                    });
                }
            }

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
                    logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect cast roles count');

                    dd($missingRoles);
//                    DB::transaction(function () use ($castRoles, $missingRoles) {
//                        $missingRoles->each(function ($missingRole) use ($castRoles) {
//                            $castRole = CastRole::create([
//                                'name' => $missingRole
//                            ]);
//
//                            $castRoles->add($castRole);
//                        });
//                    });
                }
            }

            $languageNames = $actors->pluck('language')->transform(function ($language) {
                return match ($language) {
                    'Portuguese (BR)' => 'Portuguese',
                    default => $language
                };
            })->unique();
            $languages = Language::whereIn('name', $languageNames->toArray())
                ->get();

            if ($languages->count() !== $languageNames->count()) {
                dd($languages->pluck('name'), $languageNames);
            }

            $castChunk->each(function ($newCast) use ($anime, $languages, $cast, $malID, $characters, $castRoles, $people, $animeCast) {
                $characterID = $newCast['character']['id'];
                $character = $characters->firstWhere('mal_id', '=', $characterID);

                $actorIDs = collect($newCast['actors'])->pluck('id');
                $actors = $people->filter(function (Person $person) use ($actorIDs) {
                    return $actorIDs->contains($person->mal_id);
                });

                $currentCast = collect($animeCast->where('character_id', '=', $character->id)->all());

                if ($currentCast->count() !== $actors->count()) {
                    $actorIDs = $actors->pluck('id');
                    $castPersonIDs = $currentCast->pluck('person_id');
                    $missingIDs = $actorIDs->diff($castPersonIDs)->unique();

                    if ($missingIDs->isNotEmpty()) {
                        logger()->channel('stderr')->error('🛠 [MAL_ID:ANIME:' . $malID . '] incorrect cast count');

                        $castRoleName = match ($newCast['cast_role']) {
                            'Main' => 'Protagonist',
                            'Supporting' => 'Supporting Character',
                            default => $newCast['cast_role']
                        };
                        $castRole = $castRoles->firstWhere('name', '=', $castRoleName);

                        DB::transaction(function () use ($anime, $newCast, $actors, $languages, $castRole, $character, $animeCast, $missingIDs) {
                            $missingIDs->each(function ($missingID) use ($anime, $newCast, $actors, $languages, $castRole, $character, $animeCast) {
                                $actor = $actors->firstWhere('id', '=', $missingID);
                                $newCastActor = collect($newCast['actors'])->firstWhere('id', '=', $actor->mal_id);
                                $newCastLanguage = $newCastActor['language'] ?? 'not array';
                                $languageName = match ($newCastLanguage) {
                                    'Portuguese (BR)' => 'Portuguese',
                                    default => $newCastLanguage
                                };
                                $language = $languages->firstWhere('name', '=', $languageName);

                                $newAnimeCast = AnimeCast::create([
                                    'anime_id' => $anime->id,
                                    'character_id' => $character->id,
                                    'cast_role_id' => $castRole->id,
                                    'person_id' => $missingID,
                                    'language_id' => $language->id
                                ]);

                                $animeCast->add($newAnimeCast);
                            });
                        });
                    }
                }
            });

            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done updating characters');
        }

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

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done processing characters');
        return $item;
    }
}
