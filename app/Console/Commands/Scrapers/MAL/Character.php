<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\CharacterSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Character extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_character {malID? : The id of the character. Accepts an array of comma seperated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape character data from MAL for the given MAL ID.';

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
            $urls[] = config('scraper.domains.mal.character') . '/' . $malID;
        }

        // Scrape
        Roach::startSpider(CharacterSpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
