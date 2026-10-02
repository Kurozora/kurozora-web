<?php

namespace App\Processors\TVDB;

use App\Enums\MediaCollection;
use App\Helpers\ResmushIt;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Language;
use App\Models\Season;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class EpisodeProcessor implements ItemProcessorInterface
{
    use Configurable;

    /**
     * The current item.
     *
     * @var ItemInterface|null
     */
    private ?ItemInterface $item = null;

    /**
     * The default options for the processor.
     */
    private function defaultOptions(): array
    {
        return [
            'dryRun' => false,
            'allowedSeasonIDs' => [],
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $this->item = $item;
        $dryRun = (bool) $this->option('dryRun');
        $allowedSeasonIDs = $this->option('allowedSeasonIDs') ?: [];
        $tvdbID = $item->get('tvdb_id');
        $tvdbSeasonNumber = $this->cleanSeasonNumber($item->get('season_number'));
        $tvdbEpisodeNumber = $this->cleanEpisodeNumber($item->get('episode_number'));
        $episodeNumberTotal = $this->cleanEpisodeNumber($item->get('episode_number_total'));
        $translations = $this->cleanTranslations($item->get('translations'), $episodeNumberTotal);
        $episodeDuration = $this->getDuration($item->get('episode_duration'));
        $episodeStartedAt = $this->getStartedAt($item->get('episode_started_at'));
        $episodeBannerImageURL = $item->get('episode_banner_image_url');

        // Pick the mapping whose offset bracket contains this TVDB episode.
        $season = Season::withoutGlobalScopes()
            ->whereHas('anime', function (Builder $query) use ($tvdbID) {
                $query->withoutGlobalScopes()
                    ->where('tvdb_id', '=', $tvdbID);
            })
            ->where('tvdb_season', '=', $tvdbSeasonNumber)
            ->whereRaw('COALESCE(tvdb_episode_offset, 0) < ?', [$tvdbEpisodeNumber])
            ->orderByRaw('COALESCE(tvdb_episode_offset, 0) DESC')
            ->first();

        // Narrow auto-create fallback for the common single-anime Season 1 case.
        if (empty($season) && $tvdbSeasonNumber === 1) {
            $candidates = Anime::withoutGlobalScopes()
//                ->where('id', '=', $tvdbID)
                ->where('tvdb_id', '=', $tvdbID)
                ->where('episode_count', '>', 0)
//                ->where('media_type_id', '=', 2)
                ->get();

            if ($candidates->count() === 1) {
                /** @var Anime $anime */
                $anime = $candidates->first();
                $animeStartedAt = $this->getAnimeAirDateTime($anime);

                if ($dryRun) {
                    logger()->channel('stderr')->info('🧪 [DRY-RUN] [tvdb_id:' . $tvdbID . '] Would auto-create Season 1 for anime ' . $anime->id . ' (single-anime case)');
                    $season = new Season([
                        'tvdb_season' => 1,
                        'tvdb_episode_offset' => 0,
                        'number' => 1,
                        'tv_rating_id' => $anime->tv_rating_id,
                        'is_nsfw' => $anime->is_nsfw,
                    ]);
                    $season->anime_id = $anime->id;
                } else {
                    logger()->channel('stderr')->info('🖨️ [tvdb_id:' . $tvdbID . '] Auto-creating Season 1 (single-anime case)');

                    $season = $anime->seasons()
                        ->create([
                            'tv_rating_id' => $anime->tv_rating_id,
                            'tvdb_season' => 1,
                            'tvdb_episode_offset' => 0,
                            'number' => 1,
                            'is_nsfw' => $anime->is_nsfw,
                            'started_at' => $animeStartedAt,
                            'title' => 'Season 1',
                            'synopsis' => $anime->synopsis,
                            'ja' => [
                                'title' => 'シーズン1',
                                'synopsis' => $anime->translate('ja')->synopsis,
                            ]
                        ]);
                    logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done auto-creating Season 1');
                }
            }
        }

        if (empty($season)) {
            logger()->channel('stderr')->warning(
                '⚠️ [tvdb_id:' . $tvdbID . '] No Kurozora Season has tvdb_season=' . $tvdbSeasonNumber . '. Set the mapping in Nova first (TVDB Season N≥2 or shared tvdb_id across multiple Anime rows requires manual setup).'
            );
            return $item;
        }

        if (!empty($allowedSeasonIDs) && $season->exists && !in_array((int) $season->id, array_map('intval', $allowedSeasonIDs), true)) {
            return $item;
        }

        $anime = Anime::withoutGlobalScopes()
            ->find($season->anime_id);

        if (empty($anime)) {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $tvdbID . '] Season ' . $season->id . ' has no parent anime');
            return $item;
        }

        $offset = (int) ($season->tvdb_episode_offset ?? 0);
        $episodeNumber = $tvdbEpisodeNumber - $offset;

        if ($episodeNumber <= 0) {
            logger()->channel('stderr')->info(
                'ℹ️ [tvdb_id:' . $tvdbID . '] TVDB episode ' . $tvdbEpisodeNumber . ' is before this season (offset ' . $offset . '). Skipping.'
            );
            return $item;
        }

        try {
            $animeStartedAt = $this->getAnimeAirDateTime($anime);
            $duration = $anime->duration ?: $episodeDuration;
            $episodeStartedAtDateTime = $this->updateEpisodeStartedAtTime($animeStartedAt, $episodeStartedAt);
            $episodeEndedAtDateTime = $this->updateEpisodeEndedAtTime($episodeStartedAtDateTime, $duration);
            $episodeAttributes = array_merge([
                'tv_rating_id' => $season->tv_rating_id,
                'number' => $episodeNumber,
                'number_total' => $episodeNumberTotal,
                'duration' => $duration,
                'is_nsfw' => $season->is_nsfw,
            ], $translations);
            if ($episodeStartedAtDateTime) {
                $episodeAttributes['started_at'] = $episodeStartedAtDateTime->unix();
            }
            if ($episodeEndedAtDateTime) {
                $episodeAttributes['ended_at'] = $episodeEndedAtDateTime->unix();
            }

            if ($dryRun) {
                $existingID = $season->exists
                    ? $season->episodes()->where('number', $episodeNumber)->value('id')
                    : null;
                $action = $existingID ? ('update episode ' . $existingID) : 'create episode';
                $title = $translations['en']['title'] ?? ($translations[array_key_first($translations)]['title'] ?? '');
                $seasonLabel = $season->exists ? ('season ' . $season->id) : 'auto-created Season 1';
                logger()->channel('stderr')->info(
                    '🧪 [DRY-RUN] [tvdb_id:' . $tvdbID . '] Would ' . $action
                    . ' on ' . $seasonLabel
                    . ' (number=' . $episodeNumber . ', number_total=' . $episodeNumberTotal
                    . ', duration=' . ($duration ?? 'null')
                    . ', title="' . $title . '")'
                );
                if (!empty($episodeBannerImageURL)) {
                    logger()->channel('stderr')->info('🧪 [DRY-RUN] [tvdb_id:' . $tvdbID . '] Would attach banner from ' . $episodeBannerImageURL);
                }
            } else {
                $episode = $season->episodes()->updateOrCreate([
                    'number' => $episodeNumber
                ], $episodeAttributes);

                $this->addBannerImage($episodeBannerImageURL, $episode, $tvdbID);

                logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] S' . $tvdbSeasonNumber . 'E' . $tvdbEpisodeNumber . ' → season ' . $season->id . ' episode ' . $episodeNumber);
            }
        } catch (Exception $e) {
            logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] ' . $e->getMessage());
        }

        return $item;
    }

    /**
     * Cleans the translations and returns an array.
     *
     * @param array $translations
     * @param int   $number
     *
     * @return array
     */
    protected function cleanTranslations(array $translations, int $number): array
    {
        $cleanTranslations = [];

        $enExists = current(array_filter($translations, function ($item) {
            return isset($item['code']) && $item['code'] == 'eng';
        }));
        $jaExists = current(array_filter($translations, function ($item) {
            return isset($item['code']) && $item['code'] == 'jpn';
        }));

        if (!$enExists) {
            $translations[] = [
                'title' => 'Episode ' . $number,
                'synopsis' => null,
                'code' => 'eng'
            ];
        }

        if (!$jaExists) {
            $translations[] = [
                'title' => '第' . $number . '話',
                'synopsis' => null,
                'code' => 'jpn'
            ];
        }

        foreach ($translations as $translation) {
            $code = match ($translation['code']) {
                'zhtw' => 'tw',
                default => $translation['code']
            };

            if ($language = Language::where('iso_639_3', '=', $code)
                ->orWhere('code', '=', $code)
                ->first()) {
                $title = empty($translation['title'])
                    ? ('Episode ' . $number)
                    : $translation['title'];
                $synopsis = empty($translation['synopsis'])
                    ? null
                    : $translation['synopsis'];

                if (strtolower($title) === 'tba') {
                    $title = $code === 'jpn' ? ('第' . $number . '話') : ('Episode ' . $number);
                }

                $cleanTranslations[$language->code] = [
                    'title' => $title,
                    'synopsis' => $synopsis,
                ];
            }
        }

        return $cleanTranslations;
    }

    /**
     * Cleans the season number and returns an int.
     *
     * @param null|string $seasonNumber
     *
     * @return Int
     */
    protected function cleanSeasonNumber(?string $seasonNumber): int
    {
        if (empty($seasonNumber)) {
            return 0;
        }

        return (int) str($seasonNumber)
            ->remove('Season')
            ->trim()
            ->value();
    }

    /**
     * Cleans the episode number and returns an int.
     *
     * @param null|string $episodeNumber
     *
     * @return Int
     */
    protected function cleanEpisodeNumber(?string $episodeNumber): int
    {
        if (empty($episodeNumber)) {
            return 0;
        }

        return (int) str($episodeNumber)
            ->remove('Episode')
            ->trim()
            ->value();
    }

    /**
     * Get the duration of the episode.
     *
     * @param null|string $value
     *
     * @return null|int
     */
    private function getDuration(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $seconds = 0;

        $duration = trim($value);
        if ($duration == '') {
            return $seconds;
        }

        // Count minute.
        $regex = '/\d+ minutes/';
        preg_match($regex, $duration, $match);
        if (count($match)) {
            $m = preg_split('/\s/', $match[0]);
            $seconds += (int) $m[0] * 60;
        }

        // Count seconds.
        $regex = '/\d+ seconds./';
        preg_match($regex, $duration, $match);
        if (count($match)) {
            $s = preg_split('/\s/', $match[0]);
            $seconds += (int) $s[0];
        }

        return $seconds;
    }

    /**
     * Get the first aired date of the episode.
     *
     * @param ?string $value
     *
     * @return Carbon|null
     */
    protected function getStartedAt(?string $value): ?Carbon
    {
        try {
            $date = Carbon::createFromFormat('M d, Y', $value);

            if ($date) {
                return $date->shiftTimezone('Asia/Tokyo');
            }
        } catch (Exception $exception) {
            logger()->error('getStartedAt error: ' . $exception->getMessage());
        }

        return null;
    }

    /**
     * Update the start datetime of the episode.
     *
     * @param Carbon|null $animeStartedAt
     * @param Carbon|null $episodeStartedAt
     *
     * @return Carbon|null
     */
    protected function updateEpisodeStartedAtTime(?Carbon $animeStartedAt, ?Carbon $episodeStartedAt): ?Carbon
    {
        if (empty($episodeStartedAt)) {
            return null;
        }

        if (empty($animeStartedAt)) {
            return $episodeStartedAt->setTime(9, 0);
        }

        return $episodeStartedAt->setTime($animeStartedAt->hour, $animeStartedAt->minute);
    }

    /**
     * Update the end datetime of the episode.
     *
     * @param null|Carbon $episodeStartedAt
     * @param null|int    $episodeDuration
     *
     * @return Carbon|null
     */
    protected function updateEpisodeEndedAtTime(?Carbon $episodeStartedAt, ?int $episodeDuration): ?Carbon
    {
        if (empty($episodeStartedAt)) {
            return null;
        }

        $episodeEndedAt = $episodeStartedAt->copy();

        return $episodeEndedAt->addSeconds($episodeDuration);
    }

    /**
     * Get the anime air date and time.
     *
     * @param Anime $anime
     *
     * @return Carbon|null
     */
    protected function getAnimeAirDateTime(Anime $anime): ?Carbon
    {
        $animeStartedAt = $anime->started_at;

        try {
            $animeAirTime = Carbon::createFromFormat('H:i:s', $anime->air_time, 'Asia/Tokyo');
        } catch (InvalidFormatException) {
            try {
                $animeAirTime = Carbon::createFromFormat('H:i', $anime->air_time, 'Asia/Tokyo');
            } catch (InvalidFormatException) {
                $animeAirTime = null;
            }
        }

        if (empty($animeStartedAt) && empty($animeAirTime)) {
            return null;
        } else if (empty($animeStartedAt)) {
            return null;
        } else if (empty($animeAirTime)) {
            return $animeStartedAt->setTime(9, 0);
        }

        return $animeStartedAt->setTime($animeAirTime->hour, $animeAirTime->minute);
    }

    /**
     * Download and link the given image to the specified episode.
     *
     * @param string|null           $imageUrl
     * @param Model|Builder|Episode $episode
     * @param string                $tvdbID
     *
     * @return void
     */
    private function addBannerImage(?string $imageUrl, Model|Builder|Episode $episode, string $tvdbID): void
    {
        if (!empty($imageUrl) && empty($episode->getFirstMedia(MediaCollection::Banner))) {
            if ($response = ResmushIt::compress($imageUrl)) {
                try {
                    $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
                    $episode->updateImageMedia(MediaCollection::Banner(), $response, $episode->title, [], $extension);
                    logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done creating banner');
                } catch (Exception $e) {
                    logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] ' . $e->getMessage());
                }
            } else {
                logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] Resmush failed.');
            }
        }
    }
}
