<?php

namespace App\Services;

use App\Enums\UserLibraryStatus;
use App\Enums\WatchState;
use App\Exceptions\AnimeNotInCatalogException;
use App\Jobs\ReconcilePendingScrobbles;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\PendingScrobble;
use App\Models\Provider;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Scopes\PublicScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

class ScrobbleService
{
    /**
     * The cache key prefix for the ephemeral "now watching" presence.
     */
    const string NOW_WATCHING_CACHE_KEY = 'scrobble:now-watching:';

    /**
     * The Redis key prefix deduping in-flight catalog resolution per MAL id.
     */
    const string RECONCILE_DEDUPE_KEY = 'scrobble:reconcile:';

    /**
     * The resolver mapping event payloads to episodes.
     *
     * @var EpisodeResolverService $episodeResolver
     */
    protected EpisodeResolverService $episodeResolver;

    /**
     * The catalogued provider ids keyed by host.
     *
     * @var array|null $providerHostMap
     */
    protected ?array $providerHostMap = null;

    /**
     * Create a new service instance.
     *
     * @param EpisodeResolverService $episodeResolver
     */
    public function __construct(EpisodeResolverService $episodeResolver)
    {
        $this->episodeResolver = $episodeResolver;
    }

    /**
     * Begins or resumes a watch session, tracks the anime, and publishes "now watching" presence.
     *
     * @param User  $user
     * @param array $eventPayload
     *
     * @return array
     */
    public function start(User $user, array $eventPayload): array
    {
        try {
            $episode = $this->resolveEpisode($eventPayload);
        } catch (AnimeNotInCatalogException) {
            return $this->pendingResult();
        }

        // Surface the anime as in progress the moment playback begins.
        $this->ensureTracked($user, $this->animeFor($episode));

        $result = $this->upsertSession($user, $episode, WatchState::Watching(), $eventPayload, 'start');

        $this->publishPresence($user, $episode, (float) ($eventPayload['progress'] ?? 0));

        return $result;
    }

    /**
     * Saves the session's resume position without committing a play.
     *
     * @param User  $user
     * @param array $eventPayload
     *
     * @return array
     */
    public function pause(User $user, array $eventPayload): array
    {
        // A pause that has already crossed the watched threshold commits.
        return $this->settle($user, $eventPayload, WatchState::Paused(), 'pause', false);
    }

    /**
     * The percent of runtime a play must reach to be marked watched for the user.
     *
     * @param User $user
     *
     * @return float
     */
    protected function watchedThreshold(User $user): float
    {
        $threshold = $user->relationLoaded('settings')
            ? $user->settings?->scrobble_threshold
            : $user->settings()->value('scrobble_threshold');

        return (float) ($threshold ?? config('scrobble.watched_threshold'));
    }

    /**
     * Decides whether a stopped play commits as watched or pauses with a resume position.
     *
     * @param User  $user
     * @param array $eventPayload
     *
     * @return array
     *
     * @throws ValidationException
     */
    public function stop(User $user, array $eventPayload): array
    {
        if ((float) $eventPayload['progress'] < 1) {
            throw ValidationException::withMessages([
                'progress' => __('A stop below 1% progress is ignored.'),
            ]);
        }

        return $this->settle($user, $eventPayload, WatchState::Paused(), 'pause', true);
    }

    /**
     * Commits the play when its progress crossed the user's threshold, else saves the session.
     *
     * @param User       $user
     * @param array      $eventPayload
     * @param WatchState $belowState
     * @param string     $belowAction
     * @param bool       $clearPresence
     *
     * @return array
     */
    protected function settle(User $user, array $eventPayload, WatchState $belowState, string $belowAction, bool $clearPresence): array
    {
        $progress = (float) ($eventPayload['progress'] ?? 0);
        $watchedAt = isset($eventPayload['watchedAt'])
            ? Carbon::createFromTimestamp((int) $eventPayload['watchedAt'])
            : now();
        $isCommitting = $progress >= $this->watchedThreshold($user);

        try {
            $episode = $this->resolveEpisode($eventPayload);
        } catch (AnimeNotInCatalogException $exception) {
            // Park a committing play for the reconciler.
            if ($isCommitting) {
                $this->persistPendingScrobble($user, $eventPayload, $watchedAt, $exception->malID);
            }

            if ($clearPresence) {
                $this->clearPresence($user);
            }

            return $this->pendingResult();
        }

        if ($isCommitting) {
            $watchedFromURL = $eventPayload['url'] ?? null;
            $result = DB::transaction(function () use ($user, $episode, $watchedAt, $watchedFromURL) {
                $commit = $this->commitPlay($user, $episode, $watchedAt, false, $watchedFromURL);

                $user->bumpStateVersion();

                return $commit;
            });
        } else {
            $result = $this->upsertSession($user, $episode, $belowState, $eventPayload, $belowAction);
        }

        if ($clearPresence) {
            $this->clearPresence($user);
        }

        return $result;
    }

    /**
     * Commits a batch of dated plays without dedupe windows or presence.
     *
     * @param User  $user
     * @param array $plays
     *
     * @return array
     */
    public function history(User $user, array $plays): array
    {
        return DB::transaction(function () use ($user, $plays) {
            $episodeIDs = [];
            $pendingCount = 0;

            foreach ($plays as $play) {
                $watchedAt = Carbon::createFromTimestamp((int) $play['watchedAt']);

                try {
                    $episode = $this->resolveEpisode($play);
                } catch (AnimeNotInCatalogException $exception) {
                    $this->persistPendingScrobble($user, $play, $watchedAt, $exception->malID);
                    $pendingCount++;

                    continue;
                }

                $this->commitPlay($user, $episode, $watchedAt, true);

                $episodeIDs[] = $episode->public_id;
            }

            $user->bumpStateVersion();

            return [
                'attributes' => [
                    'action' => 'history',
                    'count' => count($episodeIDs),
                    'pendingCount' => $pendingCount,
                ],
                'episodeIDs' => $episodeIDs,
            ];
        });
    }

    /**
     * Commits a single dated play outside a live session, without dedupe windows or presence.
     *
     * @param User    $user
     * @param Episode $episode
     * @param Carbon  $watchedAt
     *
     * @return array
     */
    public function commitBackfill(User $user, Episode $episode, Carbon $watchedAt): array
    {
        return DB::transaction(function () use ($user, $episode, $watchedAt) {
            $commit = $this->commitPlay($user, $episode, $watchedAt, true);

            $user->bumpStateVersion();

            return $commit;
        });
    }

    /**
     * Cancels the active "now watching" presence.
     *
     * @param User $user
     *
     * @return void
     */
    public function cancel(User $user): void
    {
        $this->clearPresence($user);
    }

    /**
     * Returns the user's library entry for the anime and creates it when absent.
     *
     * @param User        $user
     * @param Anime       $anime
     * @param Carbon|null $startedAt
     *
     * @return UserLibrary
     */
    public function ensureTracked(User $user, Anime $anime, ?Carbon $startedAt = null): UserLibrary
    {
        $libraryEntry = $user->library()
            ->where('trackable_type', '=', $anime->getMorphClass())
            ->where('trackable_id', '=', $anime->getKey())
            ->first();

        if ($libraryEntry === null) {
            $now = now();
            // The raw upsert bypasses the model's `$dateFormat`.
            $nowPrecise = $now->format('Y-m-d H:i:s.u');

            // Upsert restores soft-deleted rows by clearing `deleted_at`.
            UserLibrary::upsert([[
                'user_id' => $user->id,
                'trackable_type' => $anime->getMorphClass(),
                'trackable_id' => $anime->getKey(),
                'status' => UserLibraryStatus::InProgress,
                'started_at' => $startedAt ?? $now,
                'ended_at' => null,
                'deleted_at' => null,
                'created_at' => $nowPrecise,
                'updated_at' => $nowPrecise,
            ]], ['user_id', 'trackable_type', 'trackable_id'], ['status', 'started_at', 'ended_at', 'deleted_at', 'updated_at']);

            $libraryEntry = $user->library()
                ->where('trackable_type', '=', $anime->getMorphClass())
                ->where('trackable_id', '=', $anime->getKey())
                ->first();
            $libraryEntry->searchable();
            $user->bumpStateVersion();

            return $libraryEntry;
        }

        if ($libraryEntry->started_at === null) {
            $libraryEntry->started_at = $startedAt ?? now();
            $libraryEntry->save();
        }

        return $libraryEntry;
    }

    /**
     * Resolves the event payload to an Episode and scrapes the catalog on a miss.
     *
     * @param array $eventPayload
     *
     * @return Episode
     *
     * @throws AnimeNotInCatalogException
     */
    protected function resolveEpisode(array $eventPayload): Episode
    {
        try {
            return $this->episodeResolver->resolve($eventPayload);
        } catch (AnimeNotInCatalogException $exception) {
            if ($exception->malID === null || $this->resolutionInFlight($exception->malID)) {
                throw $exception;
            }

            if ($this->attemptInlineScrape($exception->malID)) {
                try {
                    return $this->episodeResolver->resolve($eventPayload);
                } catch (AnimeNotInCatalogException $stillBare) {
                    // The scrape didn't flesh the entry out in budget.
                    $this->queueReconciliation($stillBare->malID ?? $exception->malID);

                    throw $stillBare;
                }
            }

            $this->queueReconciliation($exception->malID);

            throw $exception;
        }
    }

    /**
     * Whether the MAL id is already being resolved on the scrape queue.
     *
     * @param int $malID
     *
     * @return bool
     */
    protected function resolutionInFlight(int $malID): bool
    {
        return (bool) Redis::exists(self::RECONCILE_DEDUPE_KEY . $malID);
    }

    /**
     * Runs the MAL scrape inline within the configured budget.
     *
     * @param int $malID
     *
     * @return bool Whether the anime is now in the catalog.
     */
    protected function attemptInlineScrape(int $malID): bool
    {
        try {
            Process::timeout((int) config('scrobble.resolve_timeout'))
                ->path(base_path())
                ->run('php artisan scrape:mal_anime ' . $malID);
        } catch (Throwable) {
            // Budget exceeded or the process failed.
        }

        return Anime::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('mal_id', '=', $malID)
            ->exists();
    }

    /**
     * Queues out-of-band catalog resolution for the MAL id once per window.
     *
     * @param int $malID
     *
     * @return void
     */
    protected function queueReconciliation(int $malID): void
    {
        if (!Redis::set(self::RECONCILE_DEDUPE_KEY . $malID, 1, 'EX', 3600, 'NX')) {
            return;
        }

        dispatch(new ReconcilePendingScrobbles($malID))
            ->afterCommit()
            ->delay(now()->addSeconds(30));
    }

    /**
     * Parks a committing play for the reconciler.
     *
     * @param User     $user
     * @param array    $eventPayload
     * @param Carbon   $watchedAt
     * @param int|null $malID
     *
     * @return void
     */
    protected function persistPendingScrobble(User $user, array $eventPayload, Carbon $watchedAt, ?int $malID): void
    {
        if ($malID === null) {
            return;
        }

        $animeIdentity = $eventPayload['anime'];

        PendingScrobble::firstOrCreate([
            'user_id' => $user->id,
            'mal_id' => $malID,
            'season' => $animeIdentity['season'] ?? null,
            'number' => (int) $animeIdentity['number'],
            'is_absolute' => (bool) ($animeIdentity['isAbsolute'] ?? false),
        ], [
            'watched_at' => $watchedAt,
        ]);
    }

    /**
     * The result answered while catalog resolution is in flight.
     *
     * @return array
     */
    protected function pendingResult(): array
    {
        return [
            'attributes' => [
                'status' => 'resolving',
            ],
            'episode' => null,
            'isPending' => true,
        ];
    }

    /**
     * Commits a watched play and opens a rewatch cycle when the episode was already completed.
     *
     * @param User        $user
     * @param Episode     $episode
     * @param Carbon      $watchedAt
     * @param bool        $isBackfill
     * @param string|null $watchedFromURL
     *
     * @return array
     */
    protected function commitPlay(User $user, Episode $episode, Carbon $watchedAt, bool $isBackfill, ?string $watchedFromURL = null): array
    {
        $watchedEpisode = $user->userWatchedEpisodes()
            ->firstWhere('episode_id', '=', $episode->id);
        $isRewatch = $watchedEpisode?->completed_at !== null;

        if ($isRewatch) {
            $this->guardDedupeWindow($watchedEpisode, $episode, $watchedAt, $isBackfill);
        }

        $libraryEntry = $this->ensureTracked($user, $this->animeFor($episode), $watchedAt);

        if ($watchedEpisode === null) {
            $watchedEpisode = $user->userWatchedEpisodes()->make();
            $watchedEpisode->episode_id = $episode->id;
        }

        if ($isRewatch) {
            // A rewatch only bumps its own counter.
            $watchedEpisode->rewatch_count = min((int) $watchedEpisode->rewatch_count + 1, (int) config('scrobble.max_rewatch_count'));

            if ($isBackfill) {
                $watchedEpisode->completed_at = $watchedEpisode->completed_at->max($watchedAt);
            }
        } else {
            $watchedEpisode->completed_at = $watchedAt;
        }

        $watchedEpisode->state = WatchState::Completed();
        $watchedEpisode->progress = 100;
        $watchedEpisode->position = null;
        $watchedEpisode->started_at = $watchedEpisode->started_at ?? $watchedAt;

        // Record where the play happened.
        $this->applyWatchOrigin($watchedEpisode, $watchedFromURL);

        $watchedEpisode->save();

        if ($this->isFinalAiredEpisode($episode)) {
            $libraryEntry->status = UserLibraryStatus::Completed;
            $libraryEntry->updateStatus(UserLibraryStatus::Completed);
        }

        if ($libraryEntry->isDirty()) {
            $libraryEntry->save();
        }

        return [
            'attributes' => [
                'action' => 'scrobble',
                'progress' => 100.0,
                'isWatched' => true,
                'resumePosition' => null,
                'rewatchCount' => (int) $watchedEpisode->rewatch_count,
            ],
            'episode' => $episode,
        ];
    }

    /**
     * Upserts the in-progress session row for a non-committing event.
     *
     * @param User       $user
     * @param Episode    $episode
     * @param WatchState $state
     * @param array      $eventPayload
     * @param string     $action
     *
     * @return array
     */
    protected function upsertSession(User $user, Episode $episode, WatchState $state, array $eventPayload, string $action): array
    {
        $watchedEpisode = $user->userWatchedEpisodes()
            ->firstWhere('episode_id', '=', $episode->id);

        // Completed plays stay completed.
        if ($watchedEpisode?->completed_at !== null) {
            $this->applyWatchOrigin($watchedEpisode, $eventPayload['url'] ?? null);

            if ($watchedEpisode->isDirty()) {
                $watchedEpisode->save();
            }

            return [
                'attributes' => [
                    'action' => $action,
                    'progress' => 100.0,
                    'isWatched' => true,
                    'resumePosition' => null,
                    'rewatchCount' => (int) $watchedEpisode->rewatch_count,
                ],
                'episode' => $episode,
            ];
        }

        $progress = (float) ($eventPayload['progress'] ?? 0);
        $position = $eventPayload['position'] ?? null;

        if ($watchedEpisode === null) {
            $watchedEpisode = $user->userWatchedEpisodes()->make();
            $watchedEpisode->episode_id = $episode->id;
        }

        // Surfaced before the fresh-play wipe.
        $previousPosition = $watchedEpisode->position;

        if ($state->is(WatchState::Watching())) {
            // A fresh play wipes stale progress.
            $watchedEpisode->progress = (int) round($progress);
            $watchedEpisode->position = $position ?? $this->derivedPosition($progress, $episode);
            $watchedEpisode->started_at = now();
        } else {
            // Multi-device reconciliation: progress is monotonic, position is last-writer.
            $watchedEpisode->progress = max((int) $watchedEpisode->progress, (int) round($progress));
            $watchedEpisode->position = $position ?? $this->derivedPosition($progress, $episode) ?? $watchedEpisode->position;
            $watchedEpisode->started_at = $watchedEpisode->started_at ?? now();
        }

        $this->applyWatchOrigin($watchedEpisode, $eventPayload['url'] ?? null);

        $watchedEpisode->state = $state;

        DB::transaction(function () use ($user, $watchedEpisode) {
            $watchedEpisode->save();

            $user->bumpStateVersion();
        });

        return [
            'attributes' => [
                'action' => $action,
                'progress' => (float) $watchedEpisode->progress,
                'isWatched' => false,
                'resumePosition' => $watchedEpisode->position,
                'previousPosition' => $previousPosition,
                'rewatchCount' => (int) $watchedEpisode->rewatch_count,
            ],
            'episode' => $episode,
        ];
    }

    /**
     * Records the watch page URL and its provider on a watch row, when present.
     *
     * @param UserWatchedEpisode $watchedEpisode
     * @param string|null        $watchedFromURL
     *
     * @return void
     */
    protected function applyWatchOrigin(UserWatchedEpisode $watchedEpisode, ?string $watchedFromURL): void
    {
        if ($watchedFromURL === null) {
            return;
        }

        $watchedEpisode->watched_from_url = $watchedFromURL;
        $watchedEpisode->provider_id = $this->providerIDFor($watchedFromURL);
    }

    /**
     * The provider serving the given watch page URL, when catalogued.
     *
     * @param string $url
     *
     * @return int|null
     */
    protected function providerIDFor(string $url): ?int
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return null;
        }

        $providerHosts = $this->providerHosts();
        $labels = explode('.', $host);

        // Walk the subdomains up.
        while (count($labels) >= 2) {
            $candidate = implode('.', $labels);

            if (array_key_exists($candidate, $providerHosts)) {
                return $providerHosts[$candidate];
            }

            array_shift($labels);
        }

        return null;
    }

    /**
     * The catalogued provider ids keyed by host.
     *
     * @return array
     */
    protected function providerHosts(): array
    {
        if ($this->providerHostMap !== null) {
            return $this->providerHostMap;
        }

        $this->providerHostMap = [];

        Provider::withoutGlobalScope(PublicScope::class)
            ->whereNotNull('url')
            ->get(['id', 'url'])
            ->each(function (Provider $provider) {
                $host = strtolower((string) parse_url($provider->url, PHP_URL_HOST));
                $host = (string) preg_replace('/^www\./', '', $host);

                if ($host !== '') {
                    $this->providerHostMap[$host] = $provider->id;
                }
            });

        return $this->providerHostMap;
    }

    /**
     * Throws when a completed play is scrobbled again inside its dedupe window.
     *
     * @param UserWatchedEpisode $watchedEpisode
     * @param Episode            $episode
     * @param Carbon             $watchedAt
     * @param bool               $isBackfill
     *
     * @return void
     */
    protected function guardDedupeWindow(UserWatchedEpisode $watchedEpisode, Episode $episode, Carbon $watchedAt, bool $isBackfill): void
    {
        if ($isBackfill) {
            return;
        }

        $expiresAt = $watchedEpisode->completed_at->copy()
            ->addSeconds(max((int) $episode->duration, 300));

        if ($watchedAt->lessThan($expiresAt)) {
            throw new ConflictHttpException(__('This episode was already scrobbled recently.'), null, 0, [
                'X-Watched-At' => (string) $watchedEpisode->completed_at->timestamp,
                'X-Expires-At' => (string) $expiresAt->timestamp,
            ]);
        }

        if ((int) $watchedEpisode->rewatch_count >= (int) config('scrobble.max_rewatch_count')) {
            throw new ConflictHttpException(__('This episode reached its rewatch limit.'));
        }
    }

    /**
     * Whether the episode is the anime's final aired regular episode.
     *
     * @param Episode $episode
     *
     * @return bool
     */
    protected function isFinalAiredEpisode(Episode $episode): bool
    {
        if ($episode->started_at === null || $episode->started_at->isFuture()) {
            return false;
        }

        $animeID = $episode->season()->withoutGlobalScopes()->value('anime_id');

        return !Episode::withoutGlobalScopes()
            ->whereNull(Episode::TABLE_NAME . '.deleted_at')
            ->whereIn('season_id', Season::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where('anime_id', '=', $animeID)
                ->select('id'))
            ->where('is_special', '=', false)
            ->where('started_at', '<=', now())
            ->where('number_total', '>', $episode->number_total)
            ->exists();
    }

    /**
     * The anime the episode belongs to.
     *
     * @param Episode $episode
     *
     * @return Anime
     */
    protected function animeFor(Episode $episode): Anime
    {
        $animeID = $episode->season()->withoutGlobalScopes()->value('anime_id');

        return Anime::withoutGlobalScopes()
            ->select(['id'])
            ->findOrFail($animeID);
    }

    /**
     * The resume position derived from the reported progress.
     *
     * @param float   $progress
     * @param Episode $episode
     *
     * @return int|null
     */
    protected function derivedPosition(float $progress, Episode $episode): ?int
    {
        if ($progress <= 0 || (int) $episode->duration <= 0) {
            return null;
        }

        return (int) round($progress / 100 * (int) $episode->duration);
    }

    /**
     * Publishes the ephemeral "now watching" presence.
     *
     * @param User    $user
     * @param Episode $episode
     * @param float   $progress
     *
     * @return void
     */
    protected function publishPresence(User $user, Episode $episode, float $progress): void
    {
        $duration = (int) $episode->duration;
        $remaining = $duration > 0 ? (int) ceil($duration * (1 - min($progress, 100) / 100)) : 0;
        $expiresAt = now()->addSeconds(max($remaining, 60));

        Cache::put(self::NOW_WATCHING_CACHE_KEY . $user->id, [
            'episodeID' => $episode->public_id,
            'startedAt' => now()->timestamp,
            'expiresAt' => $expiresAt->timestamp,
        ], $expiresAt);
    }

    /**
     * Clears the "now watching" presence.
     *
     * @param User $user
     *
     * @return void
     */
    protected function clearPresence(User $user): void
    {
        Cache::forget(self::NOW_WATCHING_CACHE_KEY . $user->id);
    }
}
