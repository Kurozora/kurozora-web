<?php

namespace App\Processors\TVDB;

use App\Enums\MediaCollection;
use App\Helpers\ResmushIt;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Language;
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

    public function processItem(ItemInterface $item): ItemInterface
    {
        $this->item = $item;
        $tvdbID = $item->get('tvdb_id');
        $translations = $this->cleanTranslations($item->get('translations'));
        $seasonNumber = $this->cleanSeasonNumber($item->get('season_number'));
        $episodeNumber = $this->cleanEpisodeNumber($item->get('episode_number'));
        $episodeNumberTotal = $this->cleanEpisodeNumber($item->get('episode_number_total'));
        $episodeDuration = $this->getDuration($item->get('episode_duration'));
        $episodeStartedAt = $this->getStartedAt($item->get('episode_started_at'));
        $episodeBannerImageURL = $item->get('episode_banner_image_url');

        logger()->channel('stderr')->info('🔄 [tvdb_id:' . $tvdbID . '] Processing episode');

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('tvdb_id', '=', $tvdbID);

//        dd([
//            'tvdb_id' => $tvdbID,
//            'translations' => $translations,
//            'season_number' => $seasonNumber,
//            'episode_number' => $episodeNumber,
//            'episode_number_total' => $episodeNumberTotal,
//            'duration' => $episodeDuration,
//            'started_at' => $this->updateEpisodeStartedAtTime($this->getAnimeAirDateTime($anime), $episodeStartedAt),
//            'banner_image' => $episodeBannerImageURL,
//        ]);

        if (empty($anime)) {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $tvdbID . '] Anime not found');
        } else {
            $season = $anime->seasons()
                ->firstWhere('number', '=', $seasonNumber);
            $animeStartedAt = $this->getAnimeAirDateTime($anime);

            if (empty($season)) {
                logger()->channel('stderr')->info('🖨️ [tvdb_id:' . $tvdbID . '] Creating season');

                $season = $anime->seasons()
                    ->create([
                        'tv_rating_id' => $anime->tv_rating_id,
                        'number' => $seasonNumber,
                        'is_nsfw' => $anime->is_nsfw,
                        'started_at' => $animeStartedAt,
                        'title' => 'Season ' . $seasonNumber,
                        'synopsis' => $anime->synopsis,
                        'ja' => [
                            'title' => 'シーズン' . $seasonNumber,
                            'synopsis' => $anime->translate('ja')->synopsis,
                        ]
                    ]);
                logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done creating season');
            }

            try {
                logger()->channel('stderr')->info('🖨️ [tvdb_id:' . $tvdbID . '] Creating episode');
                $episodeStartedAtDateTime = $this->updateEpisodeStartedAtTime($animeStartedAt, $episodeStartedAt);
                $episodeAttributes = array_merge([
                    'tv_rating_id' => $season->tv_rating_id,
                    'number' => $episodeNumber,
                    'number_total' => $episodeNumberTotal,
                    'started_at' => $episodeStartedAtDateTime,
                    'duration' => $anime->duration ?: $episodeDuration,
                    'is_nsfw' => $season->is_nsfw,
                ], $translations);

                // Update or create episode
                $episode = $season->episodes()->updateOrCreate([
                    'number' => $episodeNumber
                ], $episodeAttributes);

                // Add banner image to episode
                $this->addBannerImage($episodeBannerImageURL, $episode, $tvdbID);

                logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done creating episode');
            } catch (Exception $e) {
                logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] ' . $e->getMessage());
            }
        }

        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done processing');
        return $item;
    }

    /**
     * Cleans the translations and returns an array.
     *
     * @param array $translations
     * @return array
     */
    protected function cleanTranslations(array $translations): array
    {
        $cleanTranslations = [];
        foreach ($translations as $translation) {
            $code = match ($translation['code']) {
                'zhtw' => 'tw',
                default => $translation['code']
            };
            $language = Language::where('iso_639_3', '=', $code)
            ->orWhere('code', '=', $code)
            ->first();

            $cleanTranslations[$language->code] = [
                'title' => $translation['title'],
                'synopsis' => $translation['synopsis'],
            ];
        }
        return $cleanTranslations;
    }

    /**
     * Cleans the season number and returns an int.
     *
     * @param array $seasonNumber
     * @return Int
     */
    protected function cleanSeasonNumber(array $seasonNumber): Int
    {
        $seasonNumberString = count($seasonNumber) >= 1 ? $seasonNumber[0] : null;
        return (int) str($seasonNumberString)
            ->remove('Season')
            ->trim()
            ->value();
    }

    /**
     * Cleans the episode number and returns an int.
     *
     * @param array $episodeNumber
     * @return Int
     */
    protected function cleanEpisodeNumber(array $episodeNumber): Int
    {
        $episodeNumberString = count($episodeNumber) >= 1 ? $episodeNumber[0] : null;
        return (int) str($episodeNumberString)
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
     * @param string $value
     * @return Carbon|null
     */
    protected function getStartedAt(string $value): ?Carbon
    {
        try {
            $date = Carbon::createFromFormat('M d, Y', $value);
            if ($date) {
                return $date;
            }
        } catch (Exception $exception) {
            logger()->error('getStartedAt error: ' . $exception->getMessage());
        }

        return null;
    }

    /**
     * Update the time of the first aired date of the episode.
     *
     * @param Carbon|null $animeStartedAt
     * @param Carbon $episodeStartedAt
     * @return Carbon|null
     */
    protected function updateEpisodeStartedAtTime(?Carbon $animeStartedAt, Carbon $episodeStartedAt): ?Carbon
    {
        if (empty($animeStartedAt)) {
            return $episodeStartedAt->setTime(9, 0);
        }

        return $episodeStartedAt->setTime($animeStartedAt->hour, $animeStartedAt->minute);
    }

    /**
     * Get the anime air date and time.
     *
     * @param Anime $anime
     * @return Carbon|null
     */
    protected function getAnimeAirDateTime(Anime $anime): ?Carbon
    {
        $animeStartedAt = $anime->started_at;

        try {
            $animeAirTime = Carbon::createFromFormat('H:i:s', $anime->air_time, 'Asia/Tokyo');
        } catch (InvalidFormatException $invalidFormatException) {
            try {
                $animeAirTime = Carbon::createFromFormat('H:i', $anime->air_time, 'Asia/Tokyo');
            } catch (InvalidFormatException $invalidFormatException) {
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
     * @param string|null $imageUrl
     * @param Model|Builder|Episode $episode
     * @param string $tvdbID
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
