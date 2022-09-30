<?php

namespace App\Console\Commands\Scrapers\AnimeFillerList;

use App\Spiders\AnimeFillerList\FillerSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Filler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:anime_filler {slug? : The slug of the anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape filler data from AnimeFillerList for the given slug.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $slug = $this->argument('slug');

        if (empty($slug)) {
            $slug = $this->ask('Anime slug from the url');
        }

        if (empty($slug)) {
            $this->info('Slug is empty. Exiting...');
            return Command::INVALID;
        }

        Roach::startSpider(FillerSpider::class, new Overrides(startUrls: [
            config('scraper.domains.anime_filler_list.shows') . '/' . $slug,
        ]));

        return Command::SUCCESS;
    }
}
