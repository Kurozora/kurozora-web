<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Spiders\MAL\CompanySpider;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Magazines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_magazines
                            {--f|force : Force scraping magazines already in the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape magazine data from the MAL magazine index.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $force = $this->option('force');

        Pulse::stopRecording();
        Telescope::stopRecording();

        Roach::startSpider(CompanySpider::class, new Overrides(startUrls: [config('scraper.domains.mal.magazine')]), ['force' => $force]);

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
