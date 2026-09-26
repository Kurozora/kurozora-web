<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Models\Anime;
use App\Spiders\MAL\AnimeVideoSpider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class BackfillVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_backfill_videos
                            {--limit= : Cap the number of anime scraped}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape videos for anime that have none.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $malIDs = Anime::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereNotNull('mal_id')
            ->doesntHave('videos')
            ->when($limit !== null, function (Builder $query) use ($limit) {
                $query->limit($limit);
            })
            ->orderBy('id')
            ->pluck('mal_id');

        if ($malIDs->isEmpty()) {
            $this->info('Scraped 0 anime.');
            return Command::SUCCESS;
        }

        $urls = $malIDs->map(function ($malID) {
            return str(config('scraper.domains.mal.anime_videos'))
                ->replace(':x', $malID)
                ->value();
        })->all();

        // Backfills touch too many rows for monitoring or search indexing to be worth the overhead.
        Pulse::stopRecording();
        Telescope::stopRecording();
        Anime::disableSearchSyncing();

        Roach::startSpider(AnimeVideoSpider::class, new Overrides(startUrls: $urls));

        Anime::enableSearchSyncing();
        Pulse::startRecording();
        Telescope::startRecording();

        $this->info('Scraped ' . $malIDs->count() . ' anime.');
        return Command::SUCCESS;
    }
}
