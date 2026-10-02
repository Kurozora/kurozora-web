<?php

namespace App\Services;

use App\Exceptions\AnimeNotInCatalogException;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class EpisodeResolverService
{
    /**
     * The service materializing episodes for freshly scraped anime.
     *
     * @var EpisodeMaterializationService $episodeMaterializer
     */
    protected EpisodeMaterializationService $episodeMaterializer;

    /**
     * Create a new service instance.
     *
     * @param EpisodeMaterializationService $episodeMaterializer
     */
    public function __construct(EpisodeMaterializationService $episodeMaterializer)
    {
        $this->episodeMaterializer = $episodeMaterializer;
    }

    /**
     * The external id columns accepted as anime identity.
     */
    const array UNIQUE_ID_COLUMNS = [
        'mal' => 'mal_id',
        'anilist' => 'anilist_id',
        'kitsu' => 'kitsu_id',
        'anidb' => 'anidb_id',
    ];

    /**
     * The external id columns shared across entries, in resolution priority order.
     */
    const array SHARED_ID_COLUMNS = [
        'tvdb' => 'tvdb_id',
        'imdb' => 'imdb_id',
    ];

    /**
     * Resolves a scrobble event payload to an Episode.
     *
     * @param array $eventPayload
     *
     * @return Episode
     * @throws AnimeNotInCatalogException
     */
    public function resolve(array $eventPayload): Episode
    {
        $kurozoraID = $eventPayload['episode']['kurozoraID'] ?? null;

        if ($kurozoraID !== null) {
            $episode = Episode::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->firstWhere('public_id', '=', $kurozoraID);

            if ($episode !== null) {
                return $episode;
            }
        }

        $animeIdentity = $eventPayload['anime'] ?? null;

        if ($animeIdentity === null) {
            throw new ModelNotFoundException();
        }

        $number = (int) $animeIdentity['number'];
        $seasonNumber = isset($animeIdentity['season']) ? (int) $animeIdentity['season'] : null;
        $isAbsolute = (bool) ($animeIdentity['isAbsolute'] ?? false);

        $candidates = $this->animeCandidates($animeIdentity['ids'] ?? []);
        $hasBareCandidate = false;

        foreach ($candidates as $anime) {
            $episode = $this->episodeWithin($anime, $seasonNumber, $number, $isAbsolute);

            if ($episode === null && $this->episodeMaterializer->isMaterializable($anime)) {
                $this->episodeMaterializer->materialize($anime);
                $episode = $this->episodeWithin($anime, $seasonNumber, $number, $isAbsolute);
            } elseif ($episode === null) {
                $hasBareCandidate = true;
            }

            if ($episode !== null) {
                return $episode;
            }
        }

        if (($candidates->isEmpty() || $hasBareCandidate) && isset($animeIdentity['ids']['mal'])) {
            throw new AnimeNotInCatalogException((int) $animeIdentity['ids']['mal']);
        }

        throw new ModelNotFoundException();
    }

    /**
     * The anime entries matching the given external ids, in priority order.
     *
     * @param array $externalIDs
     *
     * @return Collection
     */
    protected function animeCandidates(array $externalIDs): Collection
    {
        foreach (self::UNIQUE_ID_COLUMNS as $key => $column) {
            if (!isset($externalIDs[$key])) {
                continue;
            }

            $anime = Anime::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->firstWhere($column, '=', $externalIDs[$key]);

            if ($anime !== null) {
                return collect([$anime]);
            }
        }

        $candidates = collect();

        foreach (self::SHARED_ID_COLUMNS as $key => $column) {
            if (!isset($externalIDs[$key])) {
                continue;
            }

            $candidates = $candidates->merge(
                Anime::withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->where($column, '=', $externalIDs[$key])
                    ->orderBy('started_at')
                    ->get()
            );
        }

        return $candidates->unique('id')->values();
    }

    /**
     * Resolves the episode inside the anime by stored coordinates.
     *
     * @param Anime    $anime
     * @param int|null $seasonNumber
     * @param int      $number
     * @param bool     $isAbsolute
     *
     * @return Episode|null
     */
    protected function episodeWithin(Anime $anime, ?int $seasonNumber, int $number, bool $isAbsolute): ?Episode
    {
        if (!$isAbsolute && $seasonNumber !== null) {
            $season = $anime->seasons()->withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->firstWhere('number', '=', $seasonNumber);

            if ($season !== null) {
                $episode = $season->episodes()->withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->firstWhere('number', '=', $number);

                if ($episode !== null) {
                    return $episode;
                }
            }

            $tvdbSeasons = $anime->seasons()->withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where('tvdb_season', '=', $seasonNumber)
                ->whereNotNull('tvdb_episode_offset')
                ->orderByDesc('tvdb_episode_offset')
                ->get();

            foreach ($tvdbSeasons as $tvdbSeason) {
                $localNumber = $number - (int) $tvdbSeason->tvdb_episode_offset;

                if ($localNumber < 1) {
                    continue;
                }

                $episode = $tvdbSeason->episodes()->withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->firstWhere('number', '=', $localNumber);

                if ($episode !== null) {
                    return $episode;
                }
            }
        }

        return Episode::withoutGlobalScopes()
            ->whereNull(Episode::TABLE_NAME . '.deleted_at')
            ->whereIn('season_id', Season::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where('anime_id', '=', $anime->id)
                ->select('id'))
            ->where('is_special', '=', false)
            ->firstWhere('number_total', '=', $number);
    }
}
