<?php

namespace App\Processors\MAL;

use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Events\BareBonesAnimeAdded;
use App\Events\BareBonesMangaAdded;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaGenre;
use App\Models\MediaRelation;
use App\Models\MediaStaff;
use App\Models\MediaStudio;
use App\Models\MediaTheme;
use App\Models\MediaType;
use App\Models\Person;
use App\Models\Relation;
use App\Models\Source;
use App\Models\StaffRole;
use App\Models\Status;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\TvRating;
use App\Spiders\MAL\Models\MangaItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

class MangaProcessor extends CustomItemProcessor
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
            MangaItem::class
        ];
    }

    /**
     * The available attributes.
     *
     * @var string[]
     */
    private array $attributes = [
        'Synonyms',
        'Japanese',
        'English',
        'German',
        'Spanish',
        'French',
        'Type',
        'Volumes',
        'Chapters',
        'Status',
        'Published',
        'Serialization',
        'Source',
        'Genre',
        'Genres',
        'Theme',
        'Themes',
        'Demographic',
        'Authors',
        'Rating',
        'Score',
        'Ranked',
        'Popularity',
        'Members',
        'Favorites',
    ];

    /**
     * The available NSFW genres.
     *
     * @var string[]
     */
    protected array $nsfwGenres = [
        'Ecchi',
        'Erotica',
        'Harem',
        'Reverse Harem',
        'Hentai',
        'Yaoi',
        'Yuri',
    ];

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        $this->item = $item;

        logger()->channel('stderr')->info('🔄 [MAL_ID:MANGA:' . $malID . '] Processing ' . $malID);

        $manga = Manga::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        $originalTitle = $item->get('originalTitle');
        $synonymTitles = $this->getSynonymTitles($manga);
        $synopsis = $this->getSynopsis($item->get('synopsis'));
        $title = $this->getAttribute('English');
        $jaTitle = $this->getAttribute('Japanese');
        $deTitle = $this->getAttribute('German');
        $esTitle = $this->getAttribute('Spanish');
        $frTitle = $this->getAttribute('French');
        $volumeCount = $this->getAttribute('Volumes');
        $chapterCount = $this->getAttribute('Chapters');
        $mediaType = $this->getAttribute('Type');
        $status = $this->getAttribute('Status');
        $source = $this->getAttribute('Source');
        $studios = $this->getAttribute('Serialization');
        $authors = $this->getAttribute('Authors');
        $genre = $this->getAttribute('Genre') ?? [];
        $genres = ($this->getAttribute('Genres') ?? []) + $genre;
        $theme = $this->getAttribute('Theme') ?? [];
        $themes = ($this->getAttribute('Themes') ?? []) + $theme;
        $tvRating = $this->getRating($this->getAttribute('Rating'), $genres, $themes);
        $isNSFW = $this->getIsNSFW($tvRating, $genres, $themes);
        $demographics = $this->getAttribute('Demographic');
        $imageURL = $item->get('imageURL');
        $published = $this->getAttribute('Published');
        $startedAt = $this->getStartedAt($published);
        $endedAt = $this->getEndedAt($published);
        $publicationDay = $this->getPublicationDay($startedAt);
        $publication = $this->getAttribute('Publication');
        $publicationTime = $this->getPublicationTime($publication);
        $publicationSeason = $this->getPublicationSeason($startedAt);
        $relations = $item->get('relations');
        $attributes = [];

        // Collect conditional attributes
        if (!empty($jaTitle)) {
            $attributes = array_merge($attributes, [
                'ja' => [
                    'title' => $jaTitle,
                    'synopsis' => null,
                ],
            ]);
        }
        if (!empty($deTitle)) {
            $attributes = array_merge($attributes, [
                'de' => [
                    'title' => $deTitle,
                    'synopsis' => null,
                ],
            ]);
        }
        if (!empty($esTitle)) {
            $attributes = array_merge($attributes, [
                'es' => [
                    'title' => $esTitle,
                    'synopsis' => null,
                ],
            ]);
        }
        if (!empty($frTitle)) {
            $attributes = array_merge($attributes, [
                'fr' => [
                    'title' => $frTitle,
                    'synopsis' => null,
                ],
            ]);
        }

//        dd(array_merge([
//            'mal_id' => $malID,
//            'original_title' => $originalTitle,
//            'synonym_titles' => $synonymTitles,
//            'title' => $title ?? $originalTitle,
//            'synopsis' => $synopsis,
//            'volume_count' => $volumeCount,
//            'chapter_count' => $chapterCount,
//            'page_count' => $chapterCount * 18,
//            'media_type_id' => $mediaType,
//            'status_id' => $status,
//            'started_at' => $startedAt,
//            'ended_at' => $endedAt,
//            'publication_day' => $publicationDay,
//            'publication_time' => $publicationTime,
//            'publication_season' => $publicationSeason,
//            'tv_rating_id' => $tvRating->id,
//            'is_nsfw' => $isNSFW,
//            'studio' => $studios,
//            'authors' => $authors,
//        ], $attributes));

        if (empty($manga)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:MANGA:' . $malID . '] Creating manga');
            $manga = Manga::withoutGlobalScopes()
                ->create(array_merge([
                    'mal_id' => $malID,
                    'original_title' => $originalTitle,
                    'synonym_titles' => $synonymTitles,
                    'title' => $title ?? $originalTitle,
                    'synopsis' => $synopsis,
                    'volume_count' => $volumeCount,
                    'chapter_count' => $chapterCount,
                    'page_count' => $chapterCount * 18,
                    'media_type_id' => $mediaType,
                    'status_id' => $status,
                    'source_id' => 2, // 2 = Original
                    'duration' => 240, // Default 240 seconds (4 minutes)
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'publication_day' => $publicationDay,
                    'publication_time' => $publicationTime,
                    'publication_season' => $publicationSeason,
                    'tv_rating_id' => $tvRating->id,
                    'is_nsfw' => $isNSFW,
                ], $attributes));
            logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done creating manga');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:MANGA:' . $malID . '] Updating attributes');
            $newTitle = $title ?? $originalTitle;
            $newVolumeCount = empty($volumeCount) ? $manga->volume_count : $volumeCount;
            $newChapterCount = empty($chapterCount) ? $manga->chapter_count : $chapterCount;
            $newPageCount = empty($manga->page_count) ? $newChapterCount * 18 : $manga->page_count;
            $newDuration = empty($manga->duration) ? 240 : $manga->duration;
            $newEndedAt = $manga->ended_at ?? $endedAt;

            $manga->update(array_merge([
                'mal_id' => $malID,
                'original_title' => $originalTitle,
                'synonym_titles' => $synonymTitles,
                'title' => $newTitle,
                'synopsis' => $synopsis,
                'volume_count' => $newVolumeCount,
                'chapter_count' => $newChapterCount,
                'page_count' => $newPageCount,
                'media_type_id' => $mediaType,
                'status_id' => $status,
                'source_id' => $source ?? 2, // 2 = Original
                'duration' => $newDuration,
                'started_at' => $startedAt,
                'ended_at' => $newEndedAt,
                'publication_day' => $publicationDay,
                'publication_time' => $publicationTime,
                'publication_season' => $publicationSeason,
                'tv_rating_id' => $tvRating->id,
                'is_nsfw' => $isNSFW,
            ], $attributes));
            logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done updating attributes');
        }

        // Add poster image
        logger()->channel('stderr')->info('🌄 [MAL_ID:MANGA:' . $malID . '] Adding poster');
        $this->addPosterImage($imageURL, $manga);
        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done adding poster');

        // Add different studio relations
        logger()->channel('stderr')->info('🏢 [MAL_ID:MANGA:' . $malID . '] Adding studios');
        $this->addStudios($studios, $manga, 'is_publisher');
        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done adding studios');

        // Add genre and theme relations
        logger()->channel('stderr')->info('🎭 [MAL_ID:MANGA:' . $malID . '] Adding genres and themes');
        $this->addGenres($genres, $manga);
        $this->addGenres($demographics, $manga);
        $this->addThemes($themes, $manga);
        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done adding genres');

        // Add author relations
        logger()->channel('stderr')->info('🧑 [MAL_ID:MANGA:' . $malID . '] Adding authors');
        $this->addAuthors($authors, $manga);
        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done adding authors');

        // Add relations
        logger()->channel('stderr')->info('↔️ [MAL_ID:MANGA:' . $malID . '] Adding relations');
        $this->addRelations($relations, $manga);
        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done adding relations');

        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done processing manga');
        return $item;
    }

    /**
     * Get the attribute
     *
     * @param string $attributeKey
     * @return array|int|Stringable|string|null
     */
    private function getAttribute(string $attributeKey): array|int|Stringable|string|null
    {
        $attributes = $this->item->get('attributes');

        /** @var Stringable[] $attributes */
        foreach ($attributes as $attribute) {
            if ($attribute->startsWith($attributeKey . ':')) {
                $uncleanAttribute = $attribute->replaceFirst($attributeKey . ': ', '');
                $value = $this->getCleanAttribute($uncleanAttribute);
                switch ($attributeKey) {
                    case 'Synonyms':
                        return explode(', ', $value);
                    case 'Japanese':
                    case 'English':
                    case 'German':
                    case 'Spanish':
                    case 'French':
                    case 'Published':
                        return empty($value) ? null : $value;
                    case 'Volumes':
                    case 'Chapters':
                        return is_numeric($value) ? (int) $value : 0;
                    case 'Type':
                        return $this->getMediaType($value);
                    case 'Status':
                        return $this->getStatus($value);
                    case 'Serialization':
                        return empty($value) ? [] : array_intersect($this->item->get('studios'), explode(', ', $value));
                    case 'Theme':
                    case 'Themes':
                    case 'Demographic':
                    case 'Genre':
                    case 'Genres':
                        $genres = explode(', ', $value);
                        foreach ($genres as $key => $genre) {
                            $genres[$key] = substr($genre, 0, strlen($genre) / 2);
                        }
                        return array_intersect($this->item->get('genres'), $genres);
                    case 'Source':
                        return $this->getSource($value);
                    case 'Duration':
                        return $this->getDuration($value);
                    case 'Authors':
                        if (!empty($value)) {
                            // Necessary since authors can have special characters, like "?", in their name
                            $value = $uncleanAttribute;
                        }

                        $authors = explode('),', $value);
                        foreach ($authors as $key => $author) {
                            $authors[$key] = [
                                'name' => str($author)->replaceMatches('/\((?:.(?!\())+$/', '')
                                    ->trim()
                                    ->value(),
                                'role' => str($author)->match('/\((?:.(?!\())+$/')
                                    ->replace(['(', ')'], '')
                                    ->trim()
                                    ->value()
                            ];
                        }

                        if (empty($author)) {
                            return [];
                        }

                        $newAuthors = [];
                        foreach ($authors as $author) {
                            $intersectedAuthor = array_intersect($this->item->get('authors'), $author);
                            $newAuthors[key($intersectedAuthor)] = $author;
                        }
                        return $newAuthors;
                    case 'Score':
                    case 'Ranked':
                    case 'Popularity':
                    case 'Members':
                    case 'Favorites':
                    default:
                        break;
                }
            }
        }

        return null;
    }

    /**
     * Cleans the attribute of unnecessary string.
     *
     * @param string $attribute
     * @return string
     */
    private function getCleanAttribute(string $attribute): string
    {
        $attribute = str_replace(['None', 'None found', ', add some', '?', 'Unknown', 'per ep.'], '', $attribute);
        return trim($attribute);
    }

    /**
     * Get the media type.
     *
     * @param string $value
     * @return int
     */
    private function getMediaType(string $value): int
    {
        $value = empty($value) ? 'Unknown' : trim($value);
        $mediaType = MediaType::firstOrCreate([
            'type' => 'manga',
            'name' => $value,
        ], [
            'description' => ''
        ]);
        return $mediaType->id;
    }

    /**
     * Get the status.
     *
     * @param string $value
     * @return int
     */
    private function getStatus(string $value): int
    {
        $value = match (strtolower($value)) {
            'not yet published' => 'Not Published Yet',
            'publishing' => 'Currently Publishing',
            'finished' => 'Finished Publishing',
            default => $value
        };

        $status = Status::where('name', '=', ucwords(trim($value)))
            ->where('type', '=', 'manga')
            ->firstOrFail();
        return $status->id;
    }

    /**
     * Get the source.
     *
     * @param string $value
     * @return int
     */
    private function getSource(string $value): int
    {
        $value = empty($value) ? 'Unknown' : trim($value);
        $source = Source::firstOrCreate([
            'name' => $value
        ], [
            'description' => ''
        ]);
        return $source->id;
    }

    /**
     * Get tv rating.
     *
     * @param string|null $value
     * @param array|null $genres
     * @param array|null $themes
     * @return TvRating
     */
    private function getRating(?string $value, ?array $genres, ?array $themes): TvRating
    {
        $tvRatingName = 'NR';
        $haystack = collect($genres)->merge($themes);

        if ($haystack->contains('Hentai') || $haystack->contains('Erotica')) {
            $tvRatingName = 'R18+';
        } else if ($haystack->contains('Ecchi')) {
            $tvRatingName = 'R15+';
        }

        return TvRating::where('name', '=', $tvRatingName)
            ->firstOrFail();
    }

    /**
     * Get the duration of the manga.
     *
     * @param string $value
     * @return int
     */
    private function getDuration(string $value): int
    {
        $seconds = 0;

        $duration = trim($value);
        if ($duration == '') {
            return $seconds;
        }

        // Count hour.
        $regex = '/\d+ hr./';
        preg_match($regex, $duration, $match);
        if (count($match)) {
            $h = preg_split('/\s/', $match[0]);
            $seconds += (int) $h[0] * 60 * 60;
        }

        // Count minute.
        $regex = '/\d+ min./';
        preg_match($regex, $duration, $match);
        if (count($match)) {
            $m = preg_split('/\s/', $match[0]);
            $seconds += (int) $m[0] * 60;
        }

        // Count seconds.
        $regex = '/\d+ sec./';
        preg_match($regex, $duration, $match);
        if (count($match)) {
            $s = preg_split('/\s/', $match[0]);
            $seconds += (int) $s[0];
        }

        return $seconds;
    }

    /**
     * Add the given genre to the manga if necessary.
     *
     * @param array|null $genres
     * @param Model|Manga|null $manga
     * @return void
     */
    private function addGenres(?array $genres, Model|Manga|null $manga): void
    {
        if (empty($genres)) {
            return;
        }

        foreach ($genres as $genreID => $genreName) {
            preg_match('/((?:^|[A-Z])[a-z]+)/', $genreName,$genreName);
            $genreName = implode('', array_unique($genreName));

            if ($genreName != 'Suspense') {
                $genre = Genre::withoutGlobalScopes()
                    ->firstOrCreate([
                        'mal_id' => $genreID,
                    ], [
                        'name' => $genreName,
                    ]);
            } else {
                $genre = Genre::withoutGlobalScopes()
                    ->firstOrCreate([
                        'name' => $genreName,
                    ], [
                        'mal_id' => $genreID,
                    ]);
            }

            $mediaGenre = $manga?->mediaGenres()->firstWhere('genre_id', '=', $genre->id);

            if (empty($mediaGenre)) {
                MediaGenre::create([
                    'model_type' => get_class($manga),
                    'model_id' => $manga?->id,
                    'genre_id' => $genre->id,
                ]);
            }
        }
    }

    /**
     * Add the given theme to the manga if necessary.
     *
     * @param array|null $themes
     * @param Model|Manga|null $manga
     * @return void
     */
    private function addThemes(?array $themes, Model|Manga|null $manga): void
    {
        if (empty($themes)) {
            return;
        }

        foreach ($themes as $themeID => $themeName) {
            preg_match('/((?:^|[A-Z])[a-z]+)/', $themeName,$themeName);
            $themeName = implode('', array_unique($themeName));

            $theme = Theme::withoutGlobalScopes()
                ->firstOrCreate([
                    'mal_id' => $themeID,
                ], [
                    'name' => $themeName,
                ]);
            $mediaTheme = $manga?->mediaThemes()->firstWhere('theme_id', '=', $theme->id);

            if (empty($mediaTheme)) {
                MediaTheme::create([
                    'model_type' => get_class($manga),
                    'model_id' => $manga?->id,
                    'theme_id' => $theme->id,
                ]);
            }
        }
    }

    /**
     * Add the given studios to the manga if necessary.
     *
     * @param array|null $malStudios
     * @param Model|Manga|null $manga
     * @param string $attribute
     * @return void
     */
    private function addStudios(?array $malStudios, Model|Manga|null $manga, string $attribute): void
    {
        if (empty($malStudios)) {
            return;
        }

        foreach ($malStudios as $malStudioID => $malStudioName) {
            $studio = Studio::withoutGlobalScopes()
                ->firstOrCreate([
                    'mal_id' => $malStudioID,
                    'type' => StudioType::Manga,
                ], [
                    'name' => $malStudioName,
                ]);
            $mediaStudio = $manga?->mediaStudios()
                ->withoutGlobalScopes()
                ->firstWhere('studio_id', '=', $studio->id);

            if (empty($mediaStudio)) {
                MediaStudio::create([
                    'model_type' => $manga?->getMorphClass(),
                    'model_id' => $manga?->id,
                    'studio_id' => $studio->id,
                    $attribute => true,
                ]);
            } else {
                $mediaStudio->update([
                    $attribute => true,
                ]);
            }
        }
    }

    /**
     * Add the given authors to the manga if necessary.
     *
     * @param array|null $malAuthors
     * @param Model|Manga|null $manga
     * @return void
     */
    private function addAuthors(?array $malAuthors, Model|Manga|null $manga): void
    {
        if (empty($malAuthors)) {
            return;
        }

        foreach ($malAuthors as $malAuthorID => $malAuthor) {
            $nameComponents = collect(explode(',', $malAuthor['name']));
            $firstName = $nameComponents->last();
            $lastName = $nameComponents->first() !== $firstName ? $nameComponents->first() : null;

            $person = Person::withoutGlobalScopes()
                ->firstOrCreate([
                    'mal_id' => $malAuthorID
                ], [
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);

            if (empty($malAuthorID)) {
                logger()->critical('Found an issue for manga: ' . $manga->id);
                logger()->critical('Author: ' . $firstName . ' ' . $lastName);
            }

            $staffRole = StaffRole::withoutGlobalScopes()
                ->firstOrCreate([
                    'name' => $malAuthor['role']
                ]);
            $mediaAuthor = $manga?->mediaStaff()->firstWhere([
                ['person_id', '=', $person->id],
                ['staff_role_id', '=', $staffRole->id]
            ]);

            if (empty($mediaAuthor)) {
                MediaStaff::create([
                    'model_type' => $manga?->getMorphClass(),
                    'model_id' => $manga?->id,
                    'person_id' => $person->id,
                    'staff_role_id' => $staffRole->id,
                ]);
            }
        }
    }

    /**
     * Add related media.
     *
     * @param array|null $relations
     * @param Model|Manga|null $manga
     * @return void
     */
    private function addRelations(?array $relations, Model|Manga|null $manga): void
    {
        $manga = clone $manga;

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
                        if ($foundAnime = Anime::firstWhere([
                            'mal_id' => $malID,
                        ])) {
                            $relatedModel = $foundAnime;
                        } else {
                            $relatedModel = Anime::create([
                                'mal_id' => $malID,
                                'original_title' => $originalTitle
                            ]);

                            event(new BareBonesAnimeAdded($relatedModel));
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
                    'model_id' => $manga->id,
                    'model_type' => $manga->getMorphClass(),
                    'relation_id' => $relationType->id,
                    'related_id' => $relatedModel->id,
                    'related_type' => $relatedModel->getMorphClass(),
                ];
            }

            MediaRelation::upsert($mediaRelations, ['model_type', 'model_id', 'relation_id', 'related_type', 'related_id']);
        }
    }

    /**
     * Get the first publishing date of the manga.
     *
     * @param string $published
     * @return Carbon|null
     */
    private function getStartedAt(string $published): ?Carbon
    {
        $regex = '/to.+/';
        return $this->getPublicationDate($regex, $published);
    }

    /**
     * Get the last publishing date of the manga.
     *
     * @param string $published
     * @return Carbon|null
     */
    private function getEndedAt(string $published): ?Carbon
    {
        $regex = '/(.+to)/';
        return $this->getPublicationDate($regex, $published);
    }

    /**
     * Get the publication date from the given string using the specified regex.
     *
     * @param string $regex
     * @param string $published
     * @return Carbon|null
     */
    private function getPublicationDate(string $regex, string $published): ?Carbon
    {
        $str = preg_replace($regex, '', $published);
        $str = trim(str_replace('to', '', $str));

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
     * Get the synonym titles.
     *
     * @param Model|Manga|null $manga
     * @return array|null
     */
    private function getSynonymTitles(Model|Manga|null $manga): ?array
    {
        $synonymTitles = $this->getAttribute('Synonyms') ?? [];
        $currentSynonymTitles = $manga?->synonym_titles?->toArray() ?? [];
        $newSynonymTitles = empty(count($synonymTitles)) ? $currentSynonymTitles : array_merge($currentSynonymTitles, $synonymTitles);

        return count($newSynonymTitles) ? array_values(array_unique($newSynonymTitles)) : null;
    }

    /**
     * The synopsis of the manga.
     *
     * @param string|null $synopsis
     * @return ?string
     */
    private function getSynopsis(?string $synopsis): ?string
    {
        $synopsis = empty(trim($synopsis)) ? null: $synopsis;

        if (!empty($synopsis)) {
            if (str($synopsis)->contains('No synopsis information')) {
                $synopsis = null;
            } else if (str($synopsis)->contains(['[Written by MAL Rewrite]'])) {
                $synopsis = str($synopsis)->replaceLast('[Written by MAL Rewrite]', 'Source: MAL');
            } else {
                $synopsis = preg_replace_array('/\(Source:[^ ]*|\)$/i', ['Source:', ''], $synopsis);
            }

            $synopsis = str_replace('<br>', '', $synopsis);
        }

        return $synopsis;
    }

    /**
     * Get the publication day.
     *
     * @param Carbon|null $startedAt
     * @return int|null
     */
    private function getPublicationDay(?Carbon $startedAt): ?int
    {
        if (empty($startedAt)) {
            return null;
        }

        return $startedAt->dayOfWeek;
    }

    /**
     * Get the publication time.
     *
     * @param string|null $publication
     * @return string|null
     */
    private function getPublicationTime(?string $publication): ?string
    {
        if (empty($publication) || $publication == 'Unknown') {
            return '07:00';
        } else if (str($publication)->contains('at')) {
            $publicationTime = trim(preg_replace('/(.+ at)/', '', $publication));
            return trim(preg_replace('/(\(.+)/', '', $publicationTime));
        }
        return '07:00';
    }

    /**
     * Get the publication season.
     *
     * @param Carbon|null $startedAt
     * @return int
     */
    private function getPublicationSeason(?Carbon $startedAt): int
    {
        return season_of_year($startedAt)->value;
    }

    /**
     * Download and link the given image to the specified manga.
     *
     * @param string|null $imageURL
     * @param Model|Builder|Manga $manga
     * @return void
     */
    private function addPosterImage(?string $imageURL, Model|Builder|Manga $manga): void
    {
        if (!empty($imageURL) && empty($manga->getFirstMedia(MediaCollection::Poster))) {
            try {
                $manga->updateImageMedia(MediaCollection::Poster(), $imageURL, $manga->original_title);
            } catch (Exception $e) {
                logger()->channel('stderr')->error($e->getMessage());
            }
        }
    }

    /**
     * Determines whether the manga is NSFW.
     *
     * @param TvRating $tvRating
     * @param array|null $genres
     * @param array|null $themes
     * @return bool
     */
    private function getIsNSFW(TvRating $tvRating, ?array $genres, ?array $themes): bool
    {
        if ($tvRating->weight == 5) {
            return true;
        }

        $isNSFW = false;
        $haystack = collect($genres)->merge($themes);

        foreach ($this->nsfwGenres as $nsfwGenre) {
            if (!$isNSFW) {
                $isNSFW = $haystack->contains($nsfwGenre);
            }
        }

        return $isNSFW;
    }
}
