<?php

namespace App\Services;

use App\Enums\DayOfWeek;
use App\Models\KDashboard\Manga as KManga;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaProcessor
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
     * @param Collection|KManga[] $kMangas
     * @return void
     */
    public function process(Collection|array $kMangas): void
    {
        foreach ($kMangas as $kManga) {
            $manga = Manga::withoutGlobalScopes()
                ->where([
                    ['mal_id', $kManga->id],
                ])->first();

            // Create or update the manga
            if (empty($manga)) {
                Manga::create([
                    'mal_id' => $kManga->id,
                    'original_title' => $kManga->title,
                    'synonym_titles' => empty($kManga->title_synonym) ? null : explode(', ', $kManga->title_synonym),
                    'title' => $this->getEnglishTitle($kManga),
                    'synopsis' => $this->getMangaSynopsis($kManga),
                    'ja' => [
                        'title' => $kManga->title_japanese,
                        'synopsis' => null,
                    ],
                    'tv_rating_id' => $this->getTVRating($kManga)->id,
                    'media_type_id' => $this->getMediaType($kManga)->id,
                    'source_id' => $this->getSource($kManga)->id,
                    'status_id' => $this->getStatus($kManga)->id,
                    'volume_count' => $kManga->volume,
                    'chapter_count' => $kManga->chapter,
                    'page_count' => $kManga->chapter * 18,
                    'started_at' => $this->getStartedAt($kManga),
                    'ended_at' => $this->getEndedAt($kManga),
                    'duration' => 240,
                    'publication_time' => Carbon::createFromFormat('H:i', '07:00', 'Asia/Tokyo'),
                    'publication_day' => $this->getPublicationDay($kManga),
                    'is_nsfw' => $this->getIsNSFW($kManga),
                ]);
            }
        }
    }

    /**
     * The English title of the manga.
     *
     * @param KManga $kManga
     * @return string
     */
    protected function getEnglishTitle(KManga $kManga): string
    {
        if (empty(trim($kManga->title_english))) {
            return $kManga->title;
        }

        return $kManga->title_english;
    }

    /**
     * The synopsis of the manga.
     *
     * @param KManga $kManga
     * @return ?string
     */
    protected function getMangaSynopsis(KManga $kManga): ?string
    {
        $kSynopsis = $kManga->synopsis;
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
     * The first date the manga published on.
     *
     * @param KManga $kManga
     * @return ?Carbon
     */
    protected function getStartedAt(KManga $kManga): ?Carbon
    {
        $startDay = $kManga->start_day;
        $startMonth = $kManga->start_month;
        $startYear = $kManga->start_year;
        $startDate = null;

        if (!empty($startYear)) {
            $startDay = empty($startDay) ? 1 : $startDay;
            $startMonth = empty($startMonth) ? 1 : $startMonth;
            $startDate = $startYear . '-' . $startMonth . '-' . $startDay;
        }

        return empty($startDate) ? null : Carbon::parse($startDate);
    }

    /**
     * The last date the manga aired on.
     *
     * @param KManga $kManga
     * @return ?Carbon
     */
    protected function getEndedAt(KManga $kManga): ?Carbon
    {
        $endDay = $kManga->end_day;
        $endMonth = $kManga->end_month;
        $endYear = $kManga->end_year;
        $endDate = null;

        if (!empty($endYear)) {
            $endDay = empty($endDay) ? 1 : $endDay;
            $endMonth = empty($endMonth) ? 1 : $endMonth;
            $endDate = $endYear . '-' . $endMonth . '-' . $endDay;
        }

        return empty($endDate) ? null : Carbon::parse($endDate);
    }

    /**
     * Returns the publication day of the manga.
     *
     * @param KManga $kManga
     * @return ?int
     */
    protected function getPublicationDay(KManga $kManga): ?int
    {
        $kAiringDay = ucfirst('monday');

        try {
            $dayOfWeek = DayOfWeek::fromKey($kAiringDay);
            return $dayOfWeek->value;
        } catch (InvalidEnumKeyException $enumKeyException) {
            return null;
        }
    }

    /**
     * Determines if the manga is NSFW.
     *
     * @param KManga $kManga
     * @return bool
     */
    protected function getIsNSFW(KManga $kManga): bool
    {
        $isNSFW = false;

        foreach ($this->nsfwGenres as $nsfwGenre) {
            if (!$isNSFW) {
                $isNSFW = $kManga->genres->contains('genre', $nsfwGenre);
            }
        }

        return $isNSFW;
    }

    /**
     * The TV rating of the manga.
     *
     * @param KManga $kManga
     * @return TvRating
     */
    protected function getTVRating(KManga $kManga): TvRating
    {
        $kTVRating = $kManga->rating;
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
     * The media type of the manga.
     *
     * @param KManga $kManga
     * @return MediaType
     */
    protected function getMediaType(KManga $kManga): MediaType
    {
        $kMediaType = $kManga->type;
        $mediaName = empty($kMediaType) ? 'Unknown' : $kMediaType->name;
        return MediaType::firstWhere([
            ['type', 'manga'],
            ['name', $mediaName],
        ]);
    }

    /**
     * Get the source of the manga.
     *
     * @param KManga $kManga
     * @return Source
     */
    protected function getSource(KManga $kManga): Source
    {
        $kSource = $kManga->source;
        $sourceName = empty($kSource) ? 'Unknown' : $kSource->source;
        return Source::firstWhere('name', $sourceName);
    }

    /**
     * Get the status of the manga.
     *
     * @param KManga $kManga
     * @return Status
     */
    protected function getStatus(KManga $kManga): Status
    {
        $kSource = $kManga->status;
        $statusName = match ($kSource->name) {
            'Not yet published' => 'Not Published Yet',
            'Publishing'        => 'Currently Publishing',
            'Finished'          => 'Finished Publishing',
            'On Hiatus'         => 'On Hiatus',
            'Discontinued'      => 'Discontinued',
        };
        return Status::firstWhere([
            ['type', 'manga'],
            ['name', $statusName],
        ]);
    }
}
