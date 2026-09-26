<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\PicturesSpider;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Pictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_pictures
                            {type : One of anime, manga, character, or people}
                            {malID? : The id of the entity. Accepts an array of comma separated IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape an entity\'s image gallery from MAL.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $configKey = match ($type) {
            'anime' => 'scraper.domains.mal.anime_pictures',
            'manga' => 'scraper.domains.mal.manga_pictures',
            'character' => 'scraper.domains.mal.character_pictures',
            'people' => 'scraper.domains.mal.people_pictures',
            default => null,
        };

        if (empty($configKey)) {
            $this->error('Type must be anime, manga, character, or people.');
            return Command::INVALID;
        }

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
            $urls[] = str(config($configKey))
                ->replace(':x', $malID)
                ->value();
        }

        // Scrape
        Pulse::stopRecording();
        Telescope::stopRecording();

        Roach::startSpider(PicturesSpider::class, new Overrides(startUrls: $urls));

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
