<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\MagazineSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Magazine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_magazine {malID? : The id of the magazine. Accepts an array of comma seperated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape magazine data from MAL for the given MAL ID.';

    /**
     * Execute the console command.
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
            $urls[] = config('scraper.domains.mal.magazine') . '/' . $malID;
        }

        // Scrape
        Roach::startSpider(MagazineSpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
