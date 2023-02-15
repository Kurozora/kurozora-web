<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\TopAnimeSpider;
use Exception;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class TopAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_top_anime 
                            {pages=1 : The number of pages to scrape}
                            {skip=0 : The number of pages to skip}
                            {--f|force : Force scraping anime already in the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape top anime data from MAL.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $pages = $this->argument('pages');
        $skip = $this->argument('skip');
        $force = $this->option('force');

        if (!is_numeric($pages)) {
            $this->info('Number of pages must be a numeric value. Adios...');
            return Command::INVALID;
        }

        $pages += $skip;
        $pages -= 1; // 0 based pagination, so first page starts at 0.
        $startPage = 0 + $skip;
        $urls = [];

        foreach (range($startPage, $pages) as $page) {
            $show = $page == 0 ? '': ('&show=' . $page * 50);
            $urls[] = config('scraper.domains.mal.top_anime') . $show;
        }

        Roach::startSpider(TopAnimeSpider::class, new Overrides(startUrls: $urls), ['force' => $force]);

        return Command::SUCCESS;
    }
}
