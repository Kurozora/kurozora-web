<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\AnimeStatSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class AnimeStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_anime_stat 
                            {malID? : The id of the anime. Accepts an array of comma separated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape anime stat from MAL.';

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
            $urls[] = str(config('scraper.domains.mal.anime_stats'))
                ->replace(':x', $malID)
                ->value();
        }

        // Scrape
        Roach::startSpider(AnimeStatSpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
