<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\AnimeSpider;
use Exception;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Anime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_anime {malID? : The id of the anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape anime data from MAL for the given MAL ID.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $malID = $this->argument('malID');

        if (empty($malID)) {
            $malID = $this->ask('MAL id');
        }

        if (empty($malID)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        } else if (!is_numeric($malID)) {
            $this->info('ID must be of a numeric value. Adios...');
            return Command::INVALID;
        }

        Roach::startSpider(AnimeSpider::class, new Overrides(startUrls: [
            config('scraper.domains.mal.anime') . '/' . $malID,
        ]));

        return Command::SUCCESS;
    }
}
