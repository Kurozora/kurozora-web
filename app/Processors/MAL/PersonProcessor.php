<?php

namespace App\Processors\MAL;

use App\Enums\AstrologicalSign;
use App\Enums\MediaCollection;
use App\Events\BareBonesMangaAdded;
use App\Events\BareBonesPersonAdded;
use App\Models\Manga;
use App\Models\MediaRelation;
use App\Models\Person;
use App\Models\Relation;
use App\Spiders\MAL\Models\PersonItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

class PersonProcessor extends CustomItemProcessor
{
    /**
     * The current item.
     *
     * @var ItemInterface|null
     */
    private ?ItemInterface $item = null;

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
        $this->item = $item;

        logger()->channel('stderr')->info('🔄 [MAL_ID:PERSON:' . $malID . '] Processing ' . $malID);

        $person = Person::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        $imageURL = $item->get('imageURL');
        $name = $this->getName($item->get('name'));
        $japaneseName = $this->getName($item->get('japaneseName'));
        $alternativeNames = $this->getAlternativeNames($person, $item->get('alternativeNames'));
        $about = $this->getAbout($item->get('about'));
        $birthdate = $this->getBirthday($item->get('birthday'));
        $websites = $item->get('websites');
        $animes = $item->get('animes');
        $mangas = $item->get('mangas');
        $staff = $item->get('staff');

        if (empty($person)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:PERSON:' . $malID . '] Creating person');
            $astrologicalSign = $this->getAstrologicalSign($birthdate);

            dd([
                'new',
                $name[0] ?? null,
                $name[1] ?? null,
                $japaneseName[0] ?? null,
                $japaneseName[1] ?? null,
                $alternativeNames,
                $about,
                $birthdate?->toDateString(),
                $astrologicalSign?->value,
                $websites,
            ]);
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
            $newWebsites = array_values(array_unique(array_merge($person->website_urls?->toArray() ?? [], $websites ?? [])));
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
        logger()->channel('stderr')->info('🌄 [MAL_ID:PERSON:' . $malID . '] Adding profile image');
        $this->addProfileImage($imageURL, $person);
        logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $malID . '] Done adding profile image');

        // Add relations
        logger()->channel('stderr')->info('↔️ [MAL_ID:PERSON:' . $malID . '] Adding relations');
//        $this->addRelations($relations, $person);
        logger()->channel('stderr')->info('✅️ [MAL_ID:PERSON:' . $malID . '] Done adding relations');

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
     * @param Model|Person|null $person
     * @param ?array            $alternativeNames
     *
     * @return array|null
     */
    private function getAlternativeNames(Model|Person|null $person, ?array $alternativeNames): ?array
    {
        if (empty($alternativeNames)) {
            return null;
        }

        $currentAlternativeNames = $person?->alternative_names?->toArray() ?? [];
        $newAlternativeNames = empty(count($alternativeNames)) ? $currentAlternativeNames : array_merge($currentAlternativeNames, $alternativeNames);

        return count($newAlternativeNames) ? array_values(array_unique($newAlternativeNames)) : null;
    }

    /**
     * Get the astrological sign of the person
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
     * @return ?string
     */
    private function getAbout(?string $about): ?string
    {
        $about = empty(trim($about)) ? null: $about;

        if (!empty($about)) {
            $about = preg_replace_array('/\(Source:[^ ]*|\)$/i', ['Source', ''], $about);
        }

        return $about;
    }

    /**
     * The birthday string of the person.
     *
     * @param string|null $birthday
     * @return ?Carbon
     */
    private function getBirthday(?string $birthday): ?Carbon
    {
        $str = empty(trim($birthday)) ? null: $birthday;

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
                } catch (Exception $exception) {}
            }
        }

        return null;
    }

    /**
     * Add related media.
     *
     * @param array|null $relations
     * @param Model|Person|null $person
     * @return void
     */
    private function addRelations(?array $relations, Model|Person|null $person): void
    {
        $person = clone $person;

        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relationTypeKey => $relationsArray) {
            $relationType = Relation::firstOrCreate([
                'name' => $relationTypeKey
            ]);
            $mediaRelations = [];

            foreach ($relationsArray as $key => $relation) {
                $malID = $relation['mal_id'];
                $originalTitle = $relation['original_title'];
                $relatedModel = null;

                // Some relationships are empty URLs or a dash "-" as title,
                // likely due to the resource being deleted, but the
                // relationship is not removed correctly.
                if (empty($malID) || empty($originalTitle)) {
                    continue;
                }

                switch ($relation['type']) {
                    case 'anime':
                        if ($foundPerson = Person::firstWhere([
                            'mal_id' => $malID,
                        ])) {
                            $relatedModel = $foundPerson;
                        } else {
                            $relatedModel = Person::create([
                                'mal_id' => $malID,
                                'original_title' => $originalTitle
                            ]);

                            event(new BareBonesPersonAdded($relatedModel));
                        }
                        break;
                    case 'manga':
                        if ($foundManga = Manga::firstWhere([
                            'mal_id' => $malID,
                        ])) {
                            $relatedModel = $foundManga;
                        } else {
                            $relatedModel = Manga::create([
                                'mal_id' => $malID,
                                'original_title' => $originalTitle
                            ]);

                            event(new BareBonesMangaAdded($relatedModel));
                        }
                        break;
                    default:
                        break;
                }

                $mediaRelations[] = [
                    'model_id' => $person->id,
                    'model_type' => $person->getMorphClass(),
                    'relation_id' => $relationType->id,
                    'related_id' => $relatedModel->id,
                    'related_type' => $relatedModel->getMorphClass(),
                ];
            }

            MediaRelation::upsert($mediaRelations, ['model_type', 'model_id', 'relation_id', 'related_type', 'related_id']);
        }
    }

    /**
     * Download and link the given image to the specified person.
     *
     * @param string|null $imageUrl
     * @param Model|Builder|Person $person
     * @return void
     */
    private function addProfileImage(?string $imageUrl, Model|Builder|Person $person): void
    {
        if (!empty($imageUrl) && empty($person->getFirstMedia(MediaCollection::Profile))) {
            try {
                $person->updateImageMedia(MediaCollection::Profile(), $imageUrl, $person->full_name);
            } catch (Exception $e) {
                logger()->channel('stderr')->error($e->getMessage());
            }
        }
    }
}
