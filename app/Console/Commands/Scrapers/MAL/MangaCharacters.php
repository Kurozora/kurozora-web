<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\CastSpider;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class MangaCharacters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_manga_characters
                            {malID? : The id of the manga. Accepts an array of comma separated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape manga characters from MAL.';

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
            $urls[] = str(config('scraper.domains.mal.manga_characters'))
                ->replace(':x', $malID)
                ->value();
        }

        // Scrape
        Pulse::stopRecording();
        Telescope::stopRecording();

        Roach::startSpider(CastSpider::class, new Overrides(startUrls: $urls));

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
