<?php

namespace App\Console\Commands\Testers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Services\ScrobbleService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class TestScrobble extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scrobble
                            {episode : The episode public ID to scrobble against}
                            {--user=2 : The user ID to scrobble as}
                            {--identity= : JSON identity block replacing episode.kurozoraID, e.g. {"anime":{"ids":{"mal":38000},"season":1,"number":5}}}
                            {--restore : Restore the watched row and library entry to their pre-run state}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exercise the scrobble endpoints through the full HTTP stack without persisting tokens or sessions.';

    /**
     * The user the requests run as.
     *
     * @var User $user
     */
    protected User $user;

    /**
     * The episode the ladder scrobbles against.
     *
     * @var Episode $episode
     */
    protected Episode $episode;

    /**
     * The anime the episode belongs to.
     *
     * @var int $animeID
     */
    protected int $animeID;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->user = User::findOrFail((int) $this->option('user'));
        $this->episode = Episode::withoutGlobalScopes()
            ->where('id', '=', $this->argument('episode'))
            ->firstOrFail();
        $this->animeID = $this->episode->season()->withoutGlobalScopes()->value('anime_id');

        // Keep the run side-effect free: no session rows, no search-index churn.
        config([
            'session.driver' => 'array',
            'scout.driver' => null,
        ]);

        // Transient token.
        Sanctum::actingAs($this->user, ['*']);

        $watchedSnapshot = $this->watchedRow()?->getAttributes();
        $librarySnapshot = $this->libraryRow()?->getAttributes();

        $this->info('Scrobbling as @' . $this->user->username . ' (ID ' . $this->user->id . ') against "' . $this->episode->title . '" (' . $this->episode->public_id . ')');
        $this->printExternalIDs();
        $this->printState('Before');

        $identity = $this->option('identity')
            ? json_decode($this->option('identity'), true)
            : ['episode' => ['kurozoraID' => $this->episode->public_id]];

        $this->step('1. start at 0%', 'POST', '/v1/me/scrobble/start', $identity + ['progress' => 0], 200);
        $this->line('   presence: ' . json_encode(Cache::get(ScrobbleService::NOW_WATCHING_CACHE_KEY . $this->user->id)));
        sleep(4);
        $this->step('2. pause at 40%', 'POST', '/v1/me/scrobble/pause', $identity + ['progress' => 40], 200);
        sleep(4);
        $this->step('3. stop at 45% (below threshold)', 'POST', '/v1/me/scrobble/stop', $identity + ['progress' => 45], 200);
        sleep(4);
        $this->step('4. stop at 90% (commit)', 'POST', '/v1/me/scrobble/stop', $identity + ['progress' => 90], 200);
        sleep(4);
        $this->step('5. stop at 90% again (dedupe window)', 'POST', '/v1/me/scrobble/stop', $identity + ['progress' => 90], 409);
        sleep(4);
        $this->step('6. stop at 0.5% (ignored)', 'POST', '/v1/me/scrobble/stop', $identity + ['progress' => 0.5], 422);
        sleep(4);
        $this->step('7. history backfill (rewatch append)', 'POST', '/v1/me/scrobble/history', [
            'plays' => [$identity + ['watchedAt' => now()->subHours(2)->timestamp]],
        ], 200);
        sleep(4);
        $this->step('8. cancel presence', 'DELETE', '/v1/me/scrobble', [], 200);
        sleep(4);

        $this->printState('After');

        if ($this->option('restore')) {
            $this->restore($watchedSnapshot, $librarySnapshot);
            $this->printState('Restored');
        }

        return Command::SUCCESS;
    }

    /**
     * Runs one ladder step and prints the outcome.
     *
     * @param string $label
     * @param string $method
     * @param string $uri
     * @param array  $payload
     * @param int    $expectedStatus
     *
     * @return void
     */
    protected function step(string $label, string $method, string $uri, array $payload, int $expectedStatus): void
    {
        $response = $this->request($method, $uri, $payload);
        $body = json_decode($response->getContent(), true);

        $status = $response->getStatusCode();
        $marker = match (true) {
            $status === $expectedStatus => '<info>✓</info>',
            $status === 202 => '<comment>⧗ resolving</comment>',
            default => '<error>✗ expected ' . $expectedStatus . '</error>',
        };

        $this->line($label . ' → ' . $status . ' ' . $marker);

        if (isset($body['data']['attributes'])) {
            $this->line('   ' . json_encode($body['data']['attributes']));
        } else if (isset($body['errors'])) {
            $this->line('   ' . json_encode($body['errors']));
        } else if ($status >= 400) {
            $this->line('   raw: ' . mb_substr($response->getContent(), 0, 500));
        }

        $resolvedEpisodeID = $body['data']['relationships']['episodes']['data'][0]['id'] ?? null;

        if ($resolvedEpisodeID !== null && $resolvedEpisodeID !== $this->episode->public_id) {
            $this->warn('   resolved to ' . $resolvedEpisodeID . ' instead of ' . $this->episode->public_id);
        }

        foreach (['X-Watched-At', 'X-Expires-At'] as $header) {
            if ($response->headers->has($header)) {
                $this->line('   ' . $header . ': ' . $response->headers->get($header));
            }
        }
    }

    /**
     * Dispatches a request through the full HTTP kernel.
     *
     * @param string $method
     * @param string $uri
     * @param array  $payload
     *
     * @return Response
     */
    protected function request(string $method, string $uri, array $payload): Response
    {
        $request = Request::create(config('app.url') . '/api' . $uri, $method, $payload, [], [], [
            // The app bundle in the User-Agent bypasses the X-API-Key client check.
            'HTTP_USER_AGENT' => config('app.name') . '/' . config('app.version') . ' (' . config('app.ios.bundle_id') . '; build:9999; macOS 26.0.0) libcurl/8.0.0',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        return app(HttpKernel::class)->handle($request);
    }

    /**
     * Prints the episode's anime with its stored external ids, plus its coordinates.
     *
     * @return void
     */
    protected function printExternalIDs(): void
    {
        $anime = Anime::withoutGlobalScopes()
            ->select(['id', 'original_title', 'mal_id', 'anilist_id', 'kitsu_id', 'anidb_id', 'tvdb_id', 'imdb_id'])
            ->find($this->animeID);
        $season = $this->episode->season()->withoutGlobalScopes()
            ->select(['id', 'number', 'tvdb_season', 'tvdb_episode_offset'])
            ->first();

        $this->line('   anime: "' . $anime->original_title . '" ' . json_encode($anime->only(['mal_id', 'anilist_id', 'kitsu_id', 'anidb_id', 'tvdb_id', 'imdb_id'])));
        $this->line('   coordinates: ' . json_encode([
            'season' => $season->number,
            'number' => $this->episode->number,
            'number_total' => $this->episode->number_total,
            'tvdb_season' => $season->tvdb_season,
            'tvdb_episode_offset' => $season->tvdb_episode_offset,
        ]));
    }

    /**
     * The user's watched row for the episode.
     *
     * @return UserWatchedEpisode|null
     */
    protected function watchedRow(): ?UserWatchedEpisode
    {
        return UserWatchedEpisode::where('user_id', '=', $this->user->id)
            ->where('episode_id', '=', $this->episode->id)
            ->first();
    }

    /**
     * The user's library entry for the episode's anime with soft-deleted rows.
     *
     * @return UserLibrary|null
     */
    protected function libraryRow(): ?UserLibrary
    {
        return UserLibrary::withTrashed()
            ->where('user_id', '=', $this->user->id)
            ->where('trackable_type', '=', Anime::class)
            ->where('trackable_id', '=', $this->animeID)
            ->first();
    }

    /**
     * Prints the watched row, library entry, and presence state.
     *
     * @param string $label
     *
     * @return void
     */
    protected function printState(string $label): void
    {
        $watchedRow = $this->watchedRow();
        $libraryRow = $this->libraryRow();
        $presence = Cache::get(ScrobbleService::NOW_WATCHING_CACHE_KEY . $this->user->id);

        $this->line('');
        $this->info($label . ':');
        $this->line('   watched row: ' . ($watchedRow ? json_encode($watchedRow->only(['state', 'progress', 'position', 'rewatch_count', 'started_at', 'completed_at'])) : 'none'));
        $this->line('   library entry: ' . ($libraryRow ? json_encode($libraryRow->only(['status', 'rewatch_count', 'started_at', 'ended_at', 'deleted_at'])) : 'none'));
        $this->line('   presence: ' . ($presence ? json_encode($presence) : 'none'));
        $this->line('');
    }

    /**
     * Restores the watched row and library entry to their pre-run snapshots.
     *
     * @param array|null $watchedSnapshot
     * @param array|null $librarySnapshot
     *
     * @return void
     */
    protected function restore(?array $watchedSnapshot, ?array $librarySnapshot): void
    {
        $watchedRow = $this->watchedRow();

        if ($watchedSnapshot !== null && $watchedRow !== null) {
            $watchedRow->setRawAttributes($watchedSnapshot);
            $watchedRow->save();
        }

        $libraryRow = $this->libraryRow();

        if ($librarySnapshot !== null && $libraryRow !== null) {
            $libraryRow->setRawAttributes($librarySnapshot);
            $libraryRow->save();
        }

        $this->user->bumpStateVersion();
        $this->info('Restored pre-run snapshots (rows created by the run are left in place).');
    }
}
