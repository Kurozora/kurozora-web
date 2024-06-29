<?php

namespace App\Processors\MAL;

use App\Enums\AstrologicalSign;
use App\Enums\MediaCollection;
use App\Events\BareBonesAnimeAdded;
use App\Events\BareBonesCharacterAdded;
use App\Events\BareBonesMangaAdded;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\StaffRole;
use App\Spiders\MAL\Models\PersonItem;
use Carbon\Carbon;
use DB;
use Exception;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;
use Throwable;

class PersonProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            PersonItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');

        logger()->channel('stderr')->info('🔄 [MAL_ID:PERSON:' . $malID . '] Processing ' . $malID);

        $person = Person::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        $imageURL = $item->get('imageURL');
        $name = $this->getName($item->get('name'));
        $japaneseName = $this->getName($item->get('japaneseName'));
        $alternativeNames = $this->getAlternativeNames($item->get('alternativeNames'), $person);
        $about = $this->getAbout($item->get('about'));
        $birthdate = $this->getBirthday($item->get('birthday'));
        $websites = $item->get('websites') ?? [];
        $animeCharacters = $item->get('animeCharacters') ?? [];
        $animeStaff = $item->get('animeStaff') ?? [];
        $mangas = $item->get('mangas') ?? [];

        if (empty($person)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:PERSON:' . $malID . '] Creating person');
            $astrologicalSign = $this->getAstrologicalSign($birthdate);

            $person = Person::withoutGlobalScopes()
                ->create([
                    'mal_id' => $malID,
                    'first_name' => $name[0] ?? null,
                    'last_name' => $name[1] ?? null,
                    'given_name' => $japaneseName[0] ?? null,
                    'family_name' => $japaneseName[1] ?? null,
                    'alternative_names' => $alternativeNames,
                    'about' => $about,
                    'birthdate' => $birthdate?->toDateString(),
                    'astrological_sign' => $astrologicalSign?->value,
                    'website_urls' => $websites,
                ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $malID . '] Done creating person');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:PERSON:' . $malID . '] Updating attributes');
            $newFirstName = empty($name[0]) ? $person->first_name : $name[0];
            $newLastName = empty($name[1]) ? $person->last_name : $name[1];
            $newGivenName = empty($japaneseName[0]) ? $person->given_name : $japaneseName[0];
            $newFamilyName = empty($japaneseName[1]) ? $person->family_name : $japaneseName[1];
            $newAlternativeNames = array_values(array_unique(array_merge($person->alternative_names?->toArray() ?? [], $alternativeNames ?? [])));
            $newWebsites = $this->getWebsites($websites, $person);
            $newBirthdate = empty($birthdate) ? $person->birthdate : $birthdate;
            $astrologicalSign = $this->getAstrologicalSign($newBirthdate);

            $person->update([
                'mal_id' => $malID,
                'first_name' => $newFirstName,
                'last_name' => $newLastName,
                'given_name' => $newGivenName,
                'family_name' => $newFamilyName,
                'alternative_names' => $newAlternativeNames,
                'about' => $about,
                'birthdate' => $newBirthdate?->toDateString(),
                'astrological_sign' => $astrologicalSign?->value,
                'website_urls' => $newWebsites,
            ]);
            logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $malID . '] Done updating attributes');
        }

        // Add poster image
        $this->addProfileImage($imageURL, $person);

        // Add anime staff
        $this->addAnimeStaff($animeStaff, $person);

        // Add anime characters
        $this->addAnimeCharacters($animeCharacters, $person);

        // Add manga staff
        $this->addMangaStaff($mangas, $person);

        logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $malID . '] Done processing person');
        return $item;
    }

    private function getName(?string $name): array
    {
        if (empty($name)) {
            return [];
        }

        return array_reverse(explode(', ', $name));
    }

    /**
     * Get the alternative names.
     *
     * @param ?array      $alternativeNames
     * @param Person|null $person
     *
     * @return array|null
     */
    private function getAlternativeNames(?array $alternativeNames, ?Person $person): ?array
    {
        if (empty($alternativeNames)) {
            return null;
        }

        $currentAlternativeNames = $person?->alternative_names?->toArray() ?? [];
        $newAlternativeNames = empty(count($alternativeNames)) ? $currentAlternativeNames : array_merge($currentAlternativeNames, $alternativeNames);

        return count($newAlternativeNames) ? array_values(array_unique($newAlternativeNames)) : null;
    }

    /**
     * Gt the websites of the person.
     *
     * @param null|array $websites
     * @param Person     $person
     *
     * @return array
     */
    private function getWebsites(?array $websites, Person $person): array
    {
        return collect($websites ?? [])
            ->merge($person->website_urls?->toArray() ?? [])
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
     * Get the astrological sign of the person.
     *
     * @param null|Carbon $birthdate
     *
     * @return null|AstrologicalSign
     */
    private function getAstrologicalSign(?Carbon $birthdate): ?AstrologicalSign
    {
        if (empty($birthdate)) {
            return null;
        }

        return AstrologicalSign::getFromDate($birthdate);
    }

    /**
     * The 'about' string of the person.
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
     * The birthday string of the person.
     *
     * @param string|null $birthday
     *
     * @return ?Carbon
     */
    private function getBirthday(?string $birthday): ?Carbon
    {
        $str = empty(trim($birthday)) ? null : $birthday;

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
     * Add anime staff relations
     *
     * @param array  $staff
     * @param Person $person
     *
     * @return void
     */
    private function addAnimeStaff(array $staff, Person $person): void
    {
        if (empty($staff)) {
            return;
        }

        $staffCollection = collect($staff);
        $person = clone $person;
        $roles = $staffCollection->pluck('roles')->flatten()->unique();
        $staffRoles = StaffRole::withoutGlobalScopes()->whereIn('name', $roles->toArray());

        // Add missing roles
        if ($staffRoles->count() !== $roles->count()) {
            logger()->channel('stderr')->error('🛠 [MAL_ID:PERSON:' . $person->mal_id . '] incorrect anime roles count');

            $missingRoles = $roles->diff($staffRoles->pluck('name'));

            dd($roles, $staffRoles->pluck('name'), $missingRoles);
//            DB::transaction(function () use ($staffRoles, $missingRoles) {
//                $missingRoles->each(function ($missingRole) use ($staffRoles) {
//                    $staffRole = StaffRole::create([
//                        'name' => $missingRole
//                    ]);
//
//                    $staffRoles->add($staffRole);
//                });
//            });
        }

        // Add missing staff
        try {
            logger()->channel('stderr')->info('↔️ [MAL_ID:PERSON:' . $person->mal_id . '] Adding anime staff');

            DB::transaction(function () use ($staffRoles, $person, $staffCollection) {
                $staffCollection->each(function ($staff) use ($person, $staffRoles) {
                    if ($foundAnime = Anime::withoutGlobalScopes()
                        ->firstWhere([
                            'mal_id' => $staff['id'],
                        ])) {
                        $anime = $foundAnime;
                    } else {
                        $anime = Anime::create([
                            'mal_id' => $staff['id'],
                            'original_title' => $staff['name']
                        ]);

                        event(new BareBonesAnimeAdded($anime));
                    }

                    $roles = $staffRoles->whereIn('name', $staff['roles'])
                        ->get();

                    $roles->each(function (StaffRole $role) use ($person, $anime) {
                        MediaStaff::withoutGlobalScopes()
                            ->firstOrCreate([
                                'model_type' => $anime->getMorphClass(),
                                'model_id' => $anime->id,
                                'person_id' => $person->id,
                                'staff_role_id' => $role->id,
                            ]);
                    });
                });
            });

            logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $person->mal_id . '] Done adding anime staff');
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:PERSON:' . $person->mal_id . '] Failed adding anime staff: ' . $e->getMessage());
        }
    }

    /**
     * Add anime characters relations
     *
     * @param array  $characters
     * @param Person $person
     *
     * @return void
     */
    private function addAnimeCharacters(array $characters, Person $person): void
    {
        if (empty($characters)) {
            return;
        }

        $charactersCollection = collect($characters);
        $person = clone $person;
        $malIDs = $charactersCollection->pluck('id')->flatten()->unique();
        $characters = Character::withoutGlobalScopes()->whereIn('mal_id', $malIDs->toArray())
            ->get();

        if ($characters->count() !== $malIDs->count()) {
            // Add missing characters
            $missingCharacters = $characters->pluck('mal_id')->diff($malIDs);

            if ($missingCharacters->isNotEmpty()) {
                try {
                    logger()->channel('stderr')->info('↔️ [MAL_ID:PERSON:' . $person->mal_id . '] Adding character');

                    DB::transaction(function () use ($missingCharacters, $person, $charactersCollection) {
                        $missingCharacters->each(function ($missingCharacter) use ($charactersCollection) {
                            $character = $charactersCollection->firstWhere('id', '=', $missingCharacter);

                            $newCharacter = Character::create([
                                'mal_id' => $character['id'],
                                'name' => $character['name']
                            ]);

                            event(new BareBonesCharacterAdded($newCharacter));
                        });
                    });

                    logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $person->mal_id . '] Done adding anime character');
                } catch (Throwable $e) {
                    logger()->channel('stderr')->error('❌ [MAL_ID:PERSON:' . $person->mal_id . '] Failed adding anime character: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Add manga staff relations
     *
     * @param array  $mangas
     * @param Person $person
     *
     * @return void
     */
    private function addMangaStaff(array $mangas, Person $person): void
    {
        if (empty($mangas)) {
            return;
        }

        $staffCollection = collect($mangas);
        $person = clone $person;
        $roles = $staffCollection->pluck('roles')->flatten()->unique();
        $staffRoles = StaffRole::withoutGlobalScopes()->whereIn('name', $roles->toArray());

        // Add missing roles
        if ($staffRoles->count() !== $roles->count()) {
            logger()->channel('stderr')->error('🛠 [MAL_ID:PERSON:' . $person->mal_id . '] incorrect manga roles count');

            $missingRoles = $roles->diff($staffRoles->pluck('name'));

            dd($roles, $staffRoles->pluck('name'), $missingRoles);
//            DB::transaction(function () use ($staffRoles, $missingRoles) {
//                $missingRoles->each(function ($missingRole) use ($staffRoles) {
//                    $staffRole = StaffRole::create([
//                        'name' => $missingRole
//                    ]);
//
//                    $staffRoles->add($staffRole);
//                });
//            });
        }

        // Add missing staff
        try {
            logger()->channel('stderr')->info('↔️ [MAL_ID:PERSON:' . $person->mal_id . '] Adding manga staff');

            DB::transaction(function () use ($staffRoles, $person, $staffCollection) {
                $staffCollection->each(function ($staff) use ($person, $staffRoles) {
                    if ($foundManga = Manga::withoutGlobalScopes()
                        ->firstWhere([
                            'mal_id' => $staff['id'],
                        ])) {
                        $manga = $foundManga;
                    } else {
                        $manga = Manga::create([
                            'mal_id' => $staff['id'],
                            'original_title' => $staff['name']
                        ]);

                        event(new BareBonesMangaAdded($manga));
                    }

                    $roles = $staffRoles->whereIn('name', $staff['roles'])
                        ->get();

                    $roles->each(function (StaffRole $role) use ($person, $manga) {
                        MediaStaff::firstOrCreate([
                            'model_type' => $manga->getMorphClass(),
                            'model_id' => $manga->id,
                            'person_id' => $person->id,
                            'staff_role_id' => $role->id,
                        ]);
                    });
                });
            });

            logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $person->mal_id . '] Done adding manga staff');
        } catch (Throwable $e) {
            logger()->channel('stderr')->error('❌ [MAL_ID:PERSON:' . $person->mal_id . '] Failed adding manga staff: ' . $e->getMessage());
        }
    }

    /**
     * Download and link the given image to the specified person.
     *
     * @param string|null $imageUrl
     * @param Person      $person
     *
     * @return void
     */
    private function addProfileImage(?string $imageUrl, Person $person): void
    {
        if (!empty($imageUrl) && empty($person->getFirstMedia(MediaCollection::Profile))) {
            try {
                logger()->channel('stderr')->info('🌄 [MAL_ID:PERSON:' . $person->mal_id . '] Adding profile image');

                $person->updateImageMedia(MediaCollection::Profile(), $imageUrl, $person->full_name);

                logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $person->mal_id . '] Done adding profile image');
            } catch (Exception $e) {
                logger()->channel('stderr')->error('❌️ [MAL_ID:PERSON:' . $person->mal_id . '] Failed adding profile image: ' . $e->getMessage());
            }
        }
    }
}
