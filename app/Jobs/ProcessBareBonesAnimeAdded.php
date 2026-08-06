<?php

namespace App\Jobs;

use App\Models\Anime;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

class ProcessBareBonesAnimeAdded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The id to process.
     *
     * @var string $malID
     */
    protected string $malID;

    /**
     * Create a new job instance.
     *
     * @param string $malID
     */
    public function __construct(string $malID)
    {
        $this->queue = 'scrape';
        $this->malID = $malID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('scrape:mal_anime', ['malID' => $this->malID]);

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $this->malID);

        if (empty($anime?->tv_rating_id)) {
            throw new RuntimeException('Failed to back-fill bare-bones anime ' . $this->malID . '.');
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
        Redis::del('mal:bb:anime:' . $this->malID);
    }
}
