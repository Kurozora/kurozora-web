<?php

namespace App\Console\Commands\Scrapers\TVDB;

use App\Spiders\TVDB\BannerSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Banner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:tvdb_banner {tvdbID? : The id of the anime. Accepts an array of comma seperated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape banner from TVDB for the given TVDB ID.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tvdbIDs = $this->argument('tvdbID');

        if (empty($tvdbIDs)) {
            $tvdbIDs = $this->ask('TVDB id');
        }

        $tvdbIDs = explode(',', $tvdbIDs);

        if (empty($tvdbIDs)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        }

        // Generate URLs
        $urls = [];
        foreach ($tvdbIDs as $tvdbID) {
            $urls[] = config('scraper.domains.tvdb.dereferrer.series') . '/' . $tvdbID;
        }

        // Scrape
        Roach::startSpider(BannerSpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
