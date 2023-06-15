<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\SeasonOfYear;
use App\Spiders\MAL\AnimeSeasonSpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class AnimeSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_anime_season 
                            {years? : The year of the season to scrape}
                            {seasons? : The seasons to scrape}
                            {--f|force : Force scraping anime already in the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape anime from anime season data from MAL.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $years = $this->argument('years');
        $seasons = $this->argument('seasons');
        $force = $this->option('force');

        if (!empty($years)) {
            $years = explode(',', $years);
            $seasons = empty($seasons) ? SeasonOfYear::asSelectArray() : explode(',', $seasons);
            $urls = [];

            foreach ($years as $year) {
                $yearRanges = explode('-', $year);

                if (count($yearRanges) == 2) {
                    $startYear = min($yearRanges);
                    $endYear = max($yearRanges);

                    foreach (range($startYear, $endYear) as $yearRange) {
                        foreach ($seasons as $seasonOfYear) {
                            $urls[] = config('scraper.domains.mal.anime_season') . '/' . $yearRange . '/' . strtolower($seasonOfYear);
                        }
                    }
                } else {
                    foreach ($seasons as $seasonOfYear) {
                        $urls[] = config('scraper.domains.mal.anime_season') . '/' . $year . '/' . strtolower($seasonOfYear);
                    }
                }
            }
        } else {
            $urls = [
                config('scraper.domains.mal.anime_season')
            ];
        }

        Roach::startSpider(AnimeSeasonSpider::class, new Overrides(startUrls: $urls), ['force' => $force]);
        return Command::SUCCESS;
    }
}
