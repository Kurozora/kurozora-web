<?php

namespace App\Processors\MAL;

use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SongType;
use App\Enums\StudioType;
use App\Events\BareBonesAnimeAdded;
use App\Events\BareBonesMangaAdded;
use App\Events\BareBonesProducerAdded;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MediaGenre;
use App\Models\MediaRelation;
use App\Models\MediaSong;
use App\Models\MediaStudio;
use App\Models\MediaTheme;
use App\Models\MediaType;
use App\Models\Relation;
use App\Models\Song;
use App\Models\Source;
use App\Models\Status;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\TvRating;
use App\Spiders\MAL\Models\AnimeItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

class AnimeProcessor extends CustomItemProcessor
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
            AnimeItem::class
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
        'Episodes',
        'Status',
        'Aired',
        'Premiered',
        'Broadcast',
        'Producers',
        'Licensors',
        'Studios',
        'Source',
        'Genre',
        'Genres',
        'Theme',
        'Themes',
        'Demographic',
        'Duration',
        'Rating',
        'Score',
        'Ranked',
        'Popularity',
        'Members',
        'Favorites',
    ];

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        $this->item = $item;

        logger()->channel('stderr')->info('🔄 [MAL_ID:ANIME:' . $malID . '] Processing ' . $malID);

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        $originalTitle = $item->get('originalTitle');
        $synonymTitles = $this->getSynonymTitles($anime);
        $synopsis = $this->getSynopsis($item->get('synopsis'));
        $title = $this->getAttribute('English');
        $jaTitle = $this->getAttribute('Japanese');
        $deTitle = $this->getAttribute('German');
        $esTitle = $this->getAttribute('Spanish');
        $frTitle = $this->getAttribute('French');
        $episodeCount = $this->getAttribute('Episodes');
        $mediaType = $this->getAttribute('Type');
        $status = $this->getAttribute('Status');
        $source = $this->getAttribute('Source');
        $tvRating = $this->getRating($this->getAttribute('Rating'));
        $isNSFW = $this->getIsNSFW($tvRating);
        $producers = $this->getAttribute('Producers');
        $licensors = $this->getAttribute('Licensors');
        $studios = $this->getAttribute('Studios');
        $genre = $this->getAttribute('Genre') ?? [];
        $genres = ($this->getAttribute('Genres') ?? []) + $genre;
        $theme = $this->getAttribute('Theme') ?? [];
        $themes = ($this->getAttribute('Themes') ?? []) + $theme;
        $demographics = $this->getAttribute('Demographic');
        $imageURL = $item->get('imageURL');
        $videoURL = $item->get('videoURL');
        $duration = $this->getAttribute('Duration');
        $aired = $this->getAttribute('Aired');
        $startedAt = $this->getStartedAt($aired);
        $endedAt = $this->getEndedAt($aired);
        $broadcast = $this->getAttribute('Broadcast');
        $airDay = $this->getAirDay($broadcast, $startedAt);
        $airTime = $this->getAirTime($broadcast);
        $airSeason = $this->getAirSeason($startedAt);
        $relations = $item->get('relations');
        $openingSongs = $item->get('openings');
        $endingSongs = $item->get('endings');
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
//            'episode_count' => $episodeCount,
//            'season_count' => 1,
//            'media_type_id' => $mediaType,
//            'status_id' => $status,
//            'source_id' => $source,
//            'video_url' => $videoURL,
//            'duration' => $duration,
//            'started_at' => $startedAt,
//            'ended_at' => $endedAt,
//            'air_day' => $airDay,
//            'air_time' => $airTime,
//            'air_season' => $airSeason,
//            'tv_rating_id' => $tvRating->id,
//            'is_nsfw' => $isNSFW,
//        ], $attributes));

        if (empty($anime)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:ANIME:' . $malID . '] Creating anime');
            $anime = Anime::withoutGlobalScopes()
                ->create(array_merge([
                    'mal_id' => $malID,
                    'original_title' => $originalTitle,
                    'synonym_titles' => $synonymTitles,
                    'title' => $title ?? $originalTitle,
                    'synopsis' => $synopsis,
                    'episode_count' => $episodeCount,
                    'season_count' => 1,
                    'media_type_id' => $mediaType,
                    'status_id' => $status,
                    'source_id' => $source,
                    'video_url' => $videoURL,
                    'duration' => $duration,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'air_day' => $airDay,
                    'air_time' => $airTime,
                    'air_season' => $airSeason,
                    'tv_rating_id' => $tvRating->id,
                    'is_nsfw' => $isNSFW,
                ], $attributes));
            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done creating anime');
        } else {
            logger()->channel('stderr')->info('🛠 [MAL_ID:ANIME:' . $malID . '] Updating attributes');
            $newTitle = $title ?? $originalTitle;
            $newEpisodeCount = empty($episodeCount) ? $anime->episode_count : $episodeCount;
            $newDuration = empty($anime->duration) ? $duration : $anime->duration;
            $newEndedAt = $anime->ended_at ?? $endedAt;

            $anime->update(array_merge([
                'mal_id' => $malID,
                'original_title' => $originalTitle,
                'synonym_titles' => $synonymTitles,
                'title' => $newTitle,
                'synopsis' => $synopsis,
                'episode_count' => $newEpisodeCount,
                'media_type_id' => $mediaType,
                'status_id' => $status,
                'source_id' => $source,
                'video_url' => $videoURL,
                'duration' => $newDuration,
                'started_at' => $startedAt,
                'ended_at' => $newEndedAt,
                'air_day' => $airDay,
                'air_time' => $airTime,
                'air_season' => $airSeason,
                'tv_rating_id' => $tvRating->id,
                'is_nsfw' => $isNSFW,
            ], $attributes));
            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done updating attributes');
        }

        // Add poster image
        logger()->channel('stderr')->info('🌄 [MAL_ID:ANIME:' . $malID . '] Adding poster');
        $this->addPosterImage($imageURL, $anime);
        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done adding poster');

        // Add different studio relations
        logger()->channel('stderr')->info('🏢 [MAL_ID:ANIME:' . $malID . '] Adding studios');
        $this->addStudios($producers, $anime, 'is_producer');
        $this->addStudios($licensors, $anime, 'is_licensor');
        $this->addStudios($studios, $anime, 'is_studio');
        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done adding studios');

        // Add genre and theme relations
        logger()->channel('stderr')->info('🎭 [MAL_ID:ANIME:' . $malID . '] Adding genres and themes');
        $this->addGenres($genres, $anime);
        $this->addGenres($demographics, $anime);
        $this->addThemes($themes, $anime);
        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done adding genres');

        // Add relations
        logger()->channel('stderr')->info('↔️ [MAL_ID:ANIME:' . $malID . '] Adding relations');
        $this->addRelations($relations, $anime);
        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done adding relations');

        // Add songs
        logger()->channel('stderr')->info('🎸 [MAL_ID:ANIME:' . $malID . '] Adding songs');
        $this->addSongs(SongType::Opening(), $openingSongs, $anime);
        $this->addSongs(SongType::Ending(), $endingSongs, $anime);
        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done adding songs');

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done processing anime');
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
                $value = $this->getCleanAttribute($attribute->replaceFirst($attributeKey . ': ', ''));

                switch ($attributeKey) {
                    case 'Synonyms':
                        return explode(', ', $value);
                    case 'Japanese':
                    case 'English':
                    case 'German':
                    case 'Spanish':
                    case 'French':
                    case 'Aired':
                    case 'Broadcast':
                    case 'Rating':
                        return empty($value) ? null : $value;
                    case 'Episodes':
                        return is_numeric($value) ? (int) $value : 0;
                    case 'Type':
                        return $this->getMediaType($value);
                    case 'Status':
                        return $this->getStatus($value);
                    case 'Producers':
                    case 'Licensors':
                    case 'Studios':
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
                    case 'Premiered':
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
        $attribute = str_replace(['None found', ', add some', '?', 'Unknown', 'per ep.'], '', $attribute);
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
            'type' => 'anime',
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
        if (strtolower($value) === 'not yet aired') {
            $value = 'Not Airing Yet';
        }

        $status = Status::where('name', '=', ucwords(trim($value)))
            ->where('type', '=', 'anime')
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
     * @param string $value
     * @return TvRating
     */
    private function getRating(string $value): TvRating
    {
        $tvRatingName = 'NR';

        if (!empty($value)) {
            $regex = '/.+-/';
            $value = str($value);
            $value = $value->match($regex);
            $value = $value->replaceLast('-', '')->trim()->value();

            $tvRatingName = match ($value) {
                'G', 'PG' => 'G',
                'PG-13' => 'PG-12',
                'R', 'R+' => 'R15+',
                'Rx' => 'R18+',
                default => 'NR',
            };
        }

        return TvRating::where('name', '=', $tvRatingName)
            ->firstOrFail();
    }

    /**
     * Get the duration of the anime.
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
     * Add the given genre to the anime if necessary.
     *
     * @param array|null $genres
     * @param Model|Anime|null $anime
     * @return void
     */
    private function addGenres(?array $genres, Model|Anime|null $anime): void
    {
        if (empty($genres)) {
            return;
        }

        foreach ($genres as $genreID => $genreName) {
            preg_match('/((?:^|[A-Z])[a-z]+)/', $genreName,$genreName);
            $genreName = implode('', array_unique($genreName));

            $genre = Genre::withoutGlobalScopes()
                ->firstOrCreate([
                    'mal_id' => $genreID,
                ], [
                    'name' => $genreName,
                ]);
            $mediaGenre = $anime?->mediaGenres()->firstWhere('genre_id', '=', $genre->id);

            if (empty($mediaGenre)) {
                MediaGenre::create([
                    'model_type' => get_class($anime),
                    'model_id' => $anime?->id,
                    'genre_id' => $genre->id,
                ]);
            }
        }
    }

    /**
     * Add the given theme to the anime if necessary.
     *
     * @param array|null $themes
     * @param Model|Anime|null $anime
     * @return void
     */
    private function addThemes(?array $themes, Model|Anime|null $anime): void
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
            $mediaTheme = $anime?->mediaThemes()->firstWhere('theme_id', '=', $theme->id);

            if (empty($mediaTheme)) {
                MediaTheme::create([
                    'model_type' => get_class($anime),
                    'model_id' => $anime?->id,
                    'theme_id' => $theme->id,
                ]);
            }
        }
    }

    /**
     * Add the given studios to the anime if necessary.
     *
     * @param array|null $malStudios
     * @param Model|Anime|null $anime
     * @param string $attribute
     * @return void
     */
    private function addStudios(?array $malStudios, Model|Anime|null $anime, string $attribute): void
    {
        if (empty($malStudios)) {
            return;
        }

        foreach ($malStudios as $malStudioID => $malStudioName) {
            $studio = Studio::withoutGlobalScopes()
                ->firstOrCreate([
                    'mal_id' => $malStudioID
                ], [
                    'name' => $malStudioName,
                    'type' => StudioType::Anime,
                ]);
            $mediaStudio = $anime?->mediaStudios()
                ->withoutGlobalScopes()
                ->firstWhere('studio_id', '=', $studio->id);

            if ($studio->wasRecentlyCreated) {
                event(new BareBonesProducerAdded($studio));
            }

            if (empty($mediaStudio)) {
                MediaStudio::create([
                    'model_type' => $anime?->getMorphClass(),
                    'model_id' => $anime?->id,
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
     * Get the first airing date of the anime.
     *
     * @param string $aired
     * @return Carbon|null
     */
    private function getStartedAt(string $aired): ?Carbon
    {
        $regex = '/to.+/';
        return $this->getAirDate($regex, $aired);
    }

    /**
     * Get the last airing date of the anime.
     *
     * @param string $aired
     * @return Carbon|null
     */
    private function getEndedAt(string $aired): ?Carbon
    {
        $regex = '/(.+to)/';
        return $this->getAirDate($regex, $aired);
    }

    /**
     * Get the air date from the given string using the specified regex.
     *
     * @param string $regex
     * @param string $aired
     * @return Carbon|null
     */
    private function getAirDate(string $regex, string $aired): ?Carbon
    {
        $str = preg_replace($regex, '', $aired);
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
     * @param Model|Anime|null $anime
     * @return array|null
     */
    private function getSynonymTitles(Model|Anime|null $anime): ?array
    {
        $synonymTitles = $this->getAttribute('Synonyms') ?? [];
        $currentSynonymTitles = $anime?->synonym_titles?->toArray() ?? [];
        $newSynonymTitles = empty(count($synonymTitles)) ? $currentSynonymTitles : array_merge($currentSynonymTitles, $synonymTitles);

        return count($newSynonymTitles) ? array_values(array_unique($newSynonymTitles)) : null;
    }

    /**
     * The synopsis of the anime.
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
     * Get the air day.
     *
     * @param null|string $broadcast
     * @param Carbon|null $startedAt
     *
     * @return int|null
     */
    private function getAirDay(?string $broadcast, ?Carbon $startedAt): ?int
    {
        if (!empty($broadcast)) {
            $dayOfWeek = str($broadcast)
                ->match('/\b(([m|M]on|[t|T]ues|[w|W]ed(nes)?|[t|T]hur(s)?|[f|F]ri|[s|S]at(ur)?|[s|S]un)(days)?)\b/')
                ->replaceLast('s', '')
                ->value();

            try {
                return DayOfWeek::fromKey($dayOfWeek)->value;
            } catch (Exception $e) {
                // Continue guessing
            }
        }

        if (!empty($startedAt)) {
            return $startedAt->dayOfWeek;
        }

        return null;
    }

    /**
     * Get the air time.
     *
     * @param string|null $broadcast
     * @return string|null
     */
    private function getAirTime(?string $broadcast): ?string
    {
        if (empty($broadcast) || $broadcast == 'Unknown') {
            return '09:00';
        } else if (str($broadcast)->contains('at')) {
            $airTime = trim(preg_replace('/(.+ at)/', '', $broadcast));
            return trim(preg_replace('/(\(.+)/', '', $airTime));
        }
        return '09:00';
    }

    /**
     * Get the air season.
     *
     * @param Carbon|null $startedAt
     * @return int
     */
    private function getAirSeason(?Carbon $startedAt): int
    {
        return season_of_year($startedAt)->value;
    }

    /**
     * Add related media.
     *
     * @param array|null $relations
     * @param Model|Anime|null $anime
     * @return void
     */
    private function addRelations(?array $relations, Model|Anime|null $anime): void
    {
        $anime = clone $anime;

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
                    'model_id' => $anime->id,
                    'model_type' => $anime->getMorphClass(),
                    'relation_id' => $relationType->id,
                    'related_id' => $relatedModel->id,
                    'related_type' => $relatedModel->getMorphClass(),
                ];
            }

            MediaRelation::upsert($mediaRelations, ['model_type', 'model_id', 'relation_id', 'related_type', 'related_id']);
        }
    }

    /**
     * Add related songs.
     *
     * @param SongType $songType
     * @param array|null $malSongs
     * @param Model|Anime|null $anime
     * @return void
     */
    private function addSongs(SongType $songType, ?array $malSongs, Model|Anime|null $anime): void
    {
        if (empty($malSongs)) {
            return;
        }

        foreach ($malSongs as $key => $malSong) {
            if (empty($malSong['title'])) {
                continue;
            }

            $song = Song::where('mal_id', '=', $malSong['mal_id']);

            if (!empty($malSong['amazon_id'])) {
                $song->orWhere('amazon_id', '=', $malSong['amazon_id']);
            }
            if (!empty($malSong['am_id'])) {
                $song->orWhere('am_id', '=', $malSong['am_id']);
            }
            if (!empty($malSong['spotify_id'])) {
                $song->orWhere('spotify_id', '=', $malSong['spotify_id']);
            }
            if (!empty($malSong['youtube_id'])) {
                $song->orWhere('youtube_id', '=', $malSong['youtube_id']);
            }

            $song = $song->first();

            if (empty($song)) {
                $song = Song::create([
                    'mal_id' => $malSong['mal_id'],
                    'amazon_id' => $malSong['amazon_id'],
                    'am_id' => $malSong['am_id'],
                    'spotify_id' => $malSong['spotify_id'],
                    'youtube_id' => $malSong['youtube_id'],
                    'original_title' => $malSong['title'],
                    'artist' => $malSong['artist'],
                ]);
            }

            MediaSong::updateOrCreate([
                'model_type' => $anime?->getMorphClass(),
                'model_id' => $anime?->id,
                'song_id' => $song->id,
                'type' => $songType->value,
            ], [
                'position' => $key + 1,
                'episodes' => $malSong['episodes']
            ]);
        }
    }

    /**
     * Download and link the given image to the specified anime.
     *
     * @param string|null $imageURL
     * @param Model|Builder|Anime $anime
     * @return void
     */
    private function addPosterImage(?string $imageURL, Model|Builder|Anime $anime): void
    {
        if (!empty($imageURL) && empty($anime->getFirstMedia(MediaCollection::Poster))) {
            try {
                $anime->updateImageMedia(MediaCollection::Poster(), $imageURL, $anime->original_title);
            } catch (Exception $e) {
                logger()->channel('stderr')->error($e->getMessage());
            }
        }
    }

    /**
     * Determines whether the anime is NSFW.
     *
     * @param TvRating $tvRating
     * @return bool
     */
    private function getIsNSFW(TvRating $tvRating): bool
    {
        return $tvRating->weight == 5;
    }
}
