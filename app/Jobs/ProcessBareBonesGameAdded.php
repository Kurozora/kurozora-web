<?php

namespace App\Jobs;

use App\Models\Game;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

class ProcessBareBonesGameAdded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The slug to process.
     *
     * @var string $slug
     */
    protected string $slug;

    /**
     * Create a new job instance.
     *
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        $this->queue = 'scrape';
        $this->slug = $slug;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('scrape:igdb_games', ['slugs' => [$this->slug]]);

        $game = Game::withoutGlobalScopes()
            ->firstWhere('igdb_slug', '=', $this->slug);

        if (empty($game?->source_id)) {
            throw new RuntimeException('Failed to back-fill bare-bones game ' . $this->slug . '.');
        }
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
        Redis::del('igdb:bb:game:' . $this->slug);
    }
}
