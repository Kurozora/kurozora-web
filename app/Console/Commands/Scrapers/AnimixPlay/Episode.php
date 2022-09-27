<?php

namespace App\Console\Commands\Scrapers\AnimixPlay;

use App\Spiders\AnimixPlaySpider;
use Exception;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Episode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:animix_episode {slug? : The slug of the anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape episode data from AnimixPlay for the given slug.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
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

        Roach::startSpider(AnimixPlaySpider::class, new Overrides(startUrls: [
            config('scraper.domains.animix_play.api') . '/' . $slug,
        ]));

        return Command::SUCCESS;
    }
}
