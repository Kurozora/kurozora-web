<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\AnimeVideoSpider;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class AnimeVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_anime_videos
                            {malID? : The id of the anime. Accepts an array of comma separated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape anime promotional videos from MAL.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $malIDs = $this->argument('malID');

        if (empty($malIDs)) {
            $malIDs = $this->ask('MAL id');
        }

        $malIDs = explode(',', $malIDs);

        if (empty($malIDs)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        }

        // Generate URLs
        $urls = [];
        foreach ($malIDs as $malID) {
            $urls[] = str(config('scraper.domains.mal.anime_videos'))
                ->replace(':x', $malID)
                ->value();
        }

        // Scrape
        Pulse::stopRecording();
        Telescope::stopRecording();

        Roach::startSpider(AnimeVideoSpider::class, new Overrides(startUrls: $urls));

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
