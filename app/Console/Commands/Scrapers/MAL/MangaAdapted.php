<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\MangaAdaptedSpider;
use Exception;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class MangaAdapted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_manga_adapted
                            {pages=1 : The number of pages to scrape}
                            {skip=0 : The number of pages to skip}
                            {--f|force : Force scraping manga already in the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape manga adapted to anime from MAL.';

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

        $startPage = 1 + (int) $skip;
        $endPage = (int) $skip + (int) $pages;
        $urls = [];

        foreach (range($startPage, $endPage) as $page) {
            $urls[] = config('scraper.domains.mal.manga_adapted') . '?type=all&page=' . $page;
        }

        Pulse::stopRecording();
        Telescope::stopRecording();

        Roach::startSpider(MangaAdaptedSpider::class, new Overrides(startUrls: $urls), ['force' => $force]);

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
