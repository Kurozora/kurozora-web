<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\PendingScrobble;
use App\Services\EpisodeResolverService;
use App\Services\ScrobbleService;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

class ReconcilePendingScrobbles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The MAL id to resolve and reconcile.
     *
     * @var int $malID
     */
    protected int $malID;

    /**
     * Create a new job instance.
     *
     * @param int $malID
     */
    public function __construct(int $malID)
    {
        $this->queue = 'scrape';
        $this->malID = $malID;
    }

    /**
     * Execute the job.
     *
     * @param EpisodeResolverService $episodeResolver
     * @param ScrobbleService        $scrobbleService
     *
     * @return void
     */
    public function handle(EpisodeResolverService $episodeResolver, ScrobbleService $scrobbleService): void
    {
        $anime = $this->anime();

        // Absent or a bare-bones stub.
        if ($anime === null || (int) $anime->episode_count === 0) {
            Artisan::call('scrape:mal_anime', ['malID' => (string) $this->malID]);

            $anime = $this->anime();
        }

        if ($anime === null) {
            throw new RuntimeException('Failed to resolve anime ' . $this->malID . ' for pending scrobbles.');
        }

        PendingScrobble::where('mal_id', '=', $this->malID)
            ->with(['user'])
            ->get()
            ->each(function (PendingScrobble $pendingScrobble) use ($episodeResolver, $scrobbleService) {
                try {
                    $episode = $episodeResolver->resolve($pendingScrobble->toEventPayload());
                } catch (ModelNotFoundException) {
                    // The coordinates point outside the entry.
                    $pendingScrobble->delete();

                    return;
                }

                if (!$pendingScrobble->user->hasWatched($episode)) {
                    $scrobbleService->commitBackfill($pendingScrobble->user, $episode, $pendingScrobble->watched_at);
                }

                $pendingScrobble->delete();
            });

        Redis::del(ScrobbleService::RECONCILE_DEDUPE_KEY . $this->malID);
    }

    /**
     * The catalog entry for the MAL id.
     *
     * @return Anime|null
     */
    protected function anime(): ?Anime
    {
        return Anime::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->firstWhere('mal_id', '=', $this->malID);
    }

    /**
     * Clears the dedupe key on permanent failure.
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Redis::del(ScrobbleService::RECONCILE_DEDUPE_KEY . $this->malID);
    }
}
