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
    protected $signature = 'scrape:tvdb_banners {tvdbID? : The id of the anime}';

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
        $tvdbID = $this->argument('tvdbID');

        if (empty($tvdbID)) {
            $tvdbID = $this->ask('TVDB id');
        }

        if (empty($tvdbID)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        } else if (!is_numeric($tvdbID)) {
            $this->info('ID must be of a numeric value. Adios...');
            return Command::INVALID;
        }

        Roach::startSpider(BannerSpider::class, new Overrides(startUrls: [
            config('scraper.domains.tvdb.dereferrer.series') . '/' . $tvdbID,
        ]));

        return Command::SUCCESS;
    }
}
