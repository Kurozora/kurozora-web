<?php

namespace App\Services;

use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Models\Anime;
use App\Models\KDashboard\Anime as KAnime;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Log;

class ImportAnimeProcessor
{
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

    /**
     * Processes the job.
     *
     * @param Collection|KAnime[] $kAnimes
     * @return void
     */
    public function process(Collection|array $kAnimes): void
    {
        foreach ($kAnimes as $kAnime) {
            $anime = Anime::withoutGlobalScopes()
                ->where([
                    ['mal_id', $kAnime->id],
                ])->first();

            // Create or update the anime
            if (empty($anime)) {
                $anime = Anime::create([
                    'mal_id' => $kAnime->id,
                    'original_title' => $kAnime->title,
                    'synonym_titles' => empty($kAnime->title_synonym) ? null : explode(', ', $kAnime->title_synonym),
                    'title' => $this->getEnglishTitle($kAnime),
                    'synopsis' => $this->getAnimeSynopsis($kAnime),
                    'ja' => [
                        'title' => $kAnime->title_japanese,
                        'synopsis' => null,
                    ],
                    'tv_rating_id' => $this->getTVRating($kAnime)->id,
                    'media_type_id' => $this->getMediaType($kAnime)->id,
                    'source_id' => $this->getSource($kAnime)->id,
                    'status_id' => $this->getStatus($kAnime)->id,
                    'episode_count' => $kAnime->episode,
                    'season_count' => $kAnime->episode ? 1 : 0,
                    'video_url' => empty($kAnime->video_url) ? null : $kAnime->video_url,
                    'started_at' => $this->getStartedAtDate($kAnime),
                    'ended_at' => $this->getEndedAtDate($kAnime),
                    'duration' => $kAnime->duration,
                    'air_time' => $kAnime->airing_time,
                    'air_day' => $this->getAiringDay($kAnime),
                    'air_season' => $this->getAiringSeason($kAnime),
                    'is_nsfw' => $this->getIsNSFW($kAnime),
                ]);
            } else {
                // Get max episode count
                $episodeCount = max($anime->episode_count, $kAnime->episode);

                // If it has season count then use that, otherwise check if it has an episode to add 1 as season count
                $seasonCount = $anime->season_count ?: ($kAnime->episode ? 1 : 0);

                // If it has video url then use that, otherwise update
                $videoURL = $anime->video_url ?: ($kAnime->video_url ?: null);

                // Get max duration
                $duration = max($anime->duration, $kAnime->duration ?: 0);

                // If it has air time then use that, otherwise update
                $airTime = $anime->air_time ?: $kAnime->airing_time;
                $airDay = $anime->air_day?->value ?: $this->getAiringDay($kAnime);
                $airSeason = $anime->air_season?->value ?: $this->getAiringSeason($kAnime);

                $currentSynonymTitles = $anime->synonym_titles?->toArray();
                $synonymTitles =  empty($kAnime->title_synonym) ? null : explode(', ', $kAnime->title_synonym);
                $newSynonymTitles = empty($synonymTitles) || empty($anime->synonym_titles?->count()) ? $currentSynonymTitles : array_merge($currentSynonymTitles, $synonymTitles);
                $uniqueSynonymTitles = count($newSynonymTitles ?? []) ? array_unique($newSynonymTitles) : null;

                $anime->update([
                    'original_title' => $kAnime->title,
                    'synonym_titles' => $uniqueSynonymTitles,
                    'title' => $this->getEnglishTitle($kAnime),
                    'synopsis' => $this->getAnimeSynopsis($kAnime),
                    'ja' => [
                        'title' => $kAnime->title_japanese,
                        'synopsis' => null,
                    ],
                    'tv_rating_id' => $this->getTVRating($kAnime)->id,
                    'media_type_id' => $this->getMediaType($kAnime)->id,
                    'source_id' => $this->getSource($kAnime)->id,
                    'status_id' => $this->getStatus($kAnime)->id,
                    'episode_count' => $episodeCount,
                    'season_count' => $seasonCount,
                    'video_url' => $videoURL,
                    'started_at' => $this->getStartedAtDate($kAnime),
                    'ended_at' => $this->getEndedAtDate($kAnime),
                    'duration' => $duration,
                    'air_time' => $airTime,
                    'air_day' => $airDay,
                    'air_season' => $airSeason,
                    'is_nsfw' => $this->getIsNSFW($kAnime),
                ]);
            }

            // Download poster when available and if not already present
            if (!empty($kAnime->image_url) && empty($anime->getFirstMedia(MediaCollection::Poster))) {
                try {
                    $anime->updateImageMedia(MediaCollection::Poster(), $kAnime->image_url, $anime->original_title);
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                }
            }
        }
    }

    /**
     * The English title of the anime.
     *
     * @param KAnime $kAnime
     * @return string
     */
    protected function getEnglishTitle(KAnime $kAnime): string
    {
        if (empty(trim($kAnime->title_english))) {
            return $kAnime->title;
        }

        return $kAnime->title_english;
    }

    /**
     * The synopsis of the anime.
     *
     * @param KAnime $kAnime
     * @return ?string
     */
    protected function getAnimeSynopsis(KAnime $kAnime): ?string
    {
        $kSynopsis = $kAnime->synopsis;
        $synopsis = empty(trim($kSynopsis)) ? null: $kSynopsis;

        if (!empty($synopsis)) {
            if (str($synopsis)->contains(['[Written by MAL Rewrite]'])) {
                $synopsis = str($synopsis)->replaceLast('[Written by MAL Rewrite]', 'Source: MAL');
            } else {
                $synopsis = preg_replace_array('/\([^ ]*|\)/i', ['Source:', ''], $synopsis);
            }
        }

        return $synopsis;
    }

    /**
     * The first date the anime aired on.
     *
     * @param KAnime $kAnime
     * @return ?Carbon
     */
    protected function getStartedAtDate(KAnime $kAnime): ?Carbon
    {
        $startDay = $kAnime->start_day;
        $startMonth = $kAnime->start_month;
        $startYear = $kAnime->start_year;
        $startDate = null;
        if (!empty($startYear)) {
            $startDay = empty($startDay) ? 1 : $startDay;
            $startMonth = empty($startMonth) ? 1 : $startMonth;
            $startDate = $startYear . '-' . $startMonth . '-' . $startDay;
        }
        return empty($startDate) ? null : Carbon::parse($startDate);
    }

    /**
     * The last date the anime aired on.
     *
     * @param KAnime $kAnime
     * @return ?Carbon
     */
    protected function getEndedAtDate(KAnime $kAnime): ?Carbon
    {
        $endDay = $kAnime->end_day;
        $endMonth = $kAnime->end_month;
        $endYear = $kAnime->end_year;
        $endDate = null;
        if (!empty($endYear)) {
            $endDay = empty($endDay) ? 1 : $endDay;
            $endMonth = empty($endMonth) ? 1 : $endMonth;
            $endDate = $endYear . '-' . $endMonth . '-' . $endDay;
        }
        return empty($endDate) ? null : Carbon::parse($endDate);
    }

    /**
     * Returns the airing day of the anime.
     *
     * @param KAnime $kAnime
     * @return ?int
     */
    protected function getAiringDay(KAnime $kAnime): ?int
    {
        $kAiringDay = ucfirst($kAnime->airing_day);

        try {
            $dayOfWeek = DayOfWeek::fromKey($kAiringDay);
            return $dayOfWeek->value;
        } catch (InvalidEnumKeyException $enumKeyException) {
            return null;
        }
    }

    /**
     * Returns airing season of the anime.
     *
     * @param KAnime $kAnime
     * @return ?int
     */
    protected function getAiringSeason(KAnime $kAnime): ?int
    {
        $premiered = $kAnime->premiered;

        if (empty($premiered)) {
            return null;
        }

        try {
            preg_match('/([a-z])\w+/', $premiered, $season);
            return SeasonOfYear::fromKey(ucfirst($season[0]))->value;
        } catch (InvalidEnumKeyException $enumKeyException) {
            return null;
        }
    }

    /**
     * Determines if the anime is NSFW.
     *
     * @param KAnime $kAnime
     * @return bool
     */
    protected function getIsNSFW(KAnime $kAnime): bool
    {
        $isNSFW = false;
        foreach ($this->nsfwGenres as $nsfwGenre) {
            if (!$isNSFW) {
                $isNSFW = $kAnime->genres->contains('genre', $nsfwGenre);
            }
        }
        return $isNSFW;
    }

    /**
     * The TV rating of the anime.
     *
     * @param KAnime $kAnime
     * @return TvRating
     */
    protected function getTVRating(KAnime $kAnime): TvRating
    {
        $kTVRating = $kAnime->rating;
        $mediaName = 'NR';
        if (!empty($kTVRating)) {
            $mediaName = match ($kTVRating->rating) {
                'G', 'PG' => 'G',
                'PG-13' => 'PG-12',
                'R', 'R+' => 'R15+',
                'Rx' => 'R18+',
                default => 'NR',
            };
        }
        return TvRating::firstWhere('name', $mediaName);
    }

    /**
     * The media type of the anime.
     *
     * @param KAnime $kAnime
     * @return MediaType
     */
    protected function getMediaType(KAnime $kAnime): MediaType
    {
        $kMediaType = $kAnime->type;
        $mediaName = empty($kMediaType) ? 'Unknown' : $kMediaType->name;
        return MediaType::firstWhere([
            ['type', 'anime'],
            ['name', $mediaName],
        ]);
    }

    /**
     * Get the source of the anime.
     *
     * @param KAnime $kAnime
     * @return Source
     */
    protected function getSource(KAnime $kAnime): Source
    {
        $kSource = $kAnime->source;
        $sourceName = empty($kSource) ? 'Unknown' : $kSource->source;
        return Source::firstWhere('name', $sourceName);
    }

    /**
     * Get the status of the anime.
     *
     * @param KAnime $kAnime
     * @return Status
     */
    protected function getStatus(KAnime $kAnime): Status
    {
        $kSource = $kAnime->status;
        $statusName = match ($kSource->name) {
            'Not yet aired'     => 'Not Airing Yet',
            'Currently Airing'  => 'Currently Airing',
            'Finished Airing'   => 'Finished Airing',
        };
        return Status::firstWhere([
            ['type', 'anime'],
            ['name', $statusName],
        ]);
    }
}
