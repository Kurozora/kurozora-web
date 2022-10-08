<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\UpcomingAnimeSpider;
use Exception;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class UpcomingAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_upcoming_anime {pages=1 : The number of pages to scrape}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape upcoming anime data from MAL.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $pages = $this->argument('pages');

        if (!is_numeric($pages)) {
            $this->info('Number of pages must be a numeric value. Adios...');
            return Command::INVALID;
        }

        $pages -= 1;
        $urls = [];

        foreach (range(0, $pages) as $page) {
            $show = $page == 0 ? '': ('&show=' . $page * 50);
            $urls[] = config('scraper.domains.mal.upcoming_anime') . $show;
        }

        Roach::startSpider(UpcomingAnimeSpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
