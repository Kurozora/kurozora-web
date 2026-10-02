<?php

namespace App\Services;

use App\Enums\EpisodeFillerKind;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use Carbon\Carbon;

class EpisodeMaterializationService
{
    /**
     * Reports whether this anime is a candidate for inline episode materialization.
     *
     * @param Anime $anime
     *
     * @return bool
     */
    public function isMaterializable(Anime $anime): bool
    {
        if ($anime->episode_count == 0 || $anime->started_at === null || $anime->season_count > 1) {
            return false;
        }

        return !$anime->episodes()->withoutGlobalScopes()->exists();
    }

    /**
     * Materializes the season and episodes for a single-season anime.
     *
     * @param Anime $anime
     *
     * @return int The number of episodes created.
     */
    public function materialize(Anime $anime): int
    {
        $airTime = $anime->air_time ?? '09:00:00';
        $startedAt = Carbon::createFromFormat('Y-m-d H:i:s', $anime->started_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC');
        $endedAt = $anime->ended_at ? Carbon::createFromFormat('Y-m-d H:i:s', $anime->ended_at->toDateString() . ' ' . $airTime, 'Asia/Tokyo')->setTimezone('UTC') : null;

        /** @var Season $season */
        $season = $anime->seasons()->withoutGlobalScopes()
            ->firstWhere([
                'number' => 1,
            ]);

        if (empty($season)) {
            $season = $anime->seasons()->withoutGlobalScopes()
                ->create([
                    'tv_rating_id' => $anime->tv_rating_id,
                    'tvdb_season' => 1,
                    'tvdb_episode_offset' => 0,
                    'is_nsfw' => $anime->is_nsfw,
                    'number' => 1,
                    'title' => 'Season 1',
                    'synopsis' => $anime->synopsis,
                    'ja' => [
                        'title' => 'シーズン1',
                        'synopsis' => null,
                    ],
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                ]);
        } else {
            $season->update([
                'tv_rating_id' => $anime->tv_rating_id,
                'is_nsfw' => $anime->is_nsfw,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
            ]);
        }

        /** @var Episode[] $episodes */
        $episodes = [];
        $sameDayRelease = $anime->started_at == $anime->ended_at;

        foreach (range(1, $anime->episode_count) as $count) {
            $seasonStartedAt = $season->started_at->copy();
            $episodeStartedAt = $sameDayRelease ? $seasonStartedAt->setTimezone('UTC') : $seasonStartedAt->addWeeks($count - 1)->setTimezone('UTC');
            $episodeEndedAt = ($episodeStartedAt->copy())
                ->addSeconds($anime->duration);

            $episode = $season->episodes()
                ->withoutGlobalScopes()
                ->firstWhere([
                    'number' => $count,
                    'number_total' => $count,
                ]);

            if (empty($episode)) {
                $episode = $season->episodes()
                    ->withoutGlobalScopes()
                    ->create([
                        'tv_rating_id' => $anime->tv_rating_id,
                        'is_nsfw' => $anime->is_nsfw,
                        'number' => $count,
                        'number_total' => $count,
                        'title' => 'Episode ' . $count,
                        'synopsis' => null,
                        'ja' => [
                            'title' => '第' . $count . '話',
                            'synopsis' => null,
                        ],
                        'duration' => $anime->duration,
                        'filler_kind' => EpisodeFillerKind::MangaCanon,
                        'is_verified' => false,
                        'is_premiere' => $count == 1,
                        'is_finale' => $count == $anime->episode_count,
                        'started_at' => $episodeStartedAt,
                        'ended_at' => $episodeEndedAt,
                    ]);
            }

            $episodes[] = $episode;
        }

        foreach ($episodes as $key => $episode) {
            $nextEpisode = null;
            $previousEpisode = null;

            if ($key != count($episodes) - 1) {
                $nextEpisode = $episodes[$key + 1]->id;
            }

            if ($key != 0) {
                $previousEpisode = $episodes[$key - 1]->id;
            }

            $episode->update([
                'next_episode_id' => $nextEpisode,
                'previous_episode_id' => $previousEpisode,
            ]);
        }

        $anime->updateQuietly([
            'season_count' => 1,
        ]);

        return count($episodes);
    }
}
