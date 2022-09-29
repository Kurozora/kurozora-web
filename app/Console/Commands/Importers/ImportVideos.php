<?php

namespace App\Console\Commands\Importers;

use App\Models\Anime;
use App\Spiders\AnimixPlaySpider;
use Illuminate\Console\Command;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class ImportVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:videos {slug? : The slug of the anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports videos for episodes of the specified anime.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $slug = $this->argument('slug');
        $anime = Anime::on('elb')
            ->withoutGlobalScopes()
            ->firstWhere('animix_id', '=', $slug);

        if (empty($slug)) {
            $slug = $this->ask('Kurozora Anime slug from the url');
        }

        if (empty($slug)) {
            $this->info('Slug is empty. Exiting...');
            return Command::INVALID;
        }

        if (empty($anime)) {
            $this->info('Anime [' . $slug . '] not found. Exiting...');
            return Command::INVALID;
        }

        $episodeCount = $anime->episodes()->count();
        $urls = [];

        foreach (range(1, $episodeCount) as $episodeNumber) {
            $urls[] = config('scraper.domains.animix_play.api') . '/' . $slug . '/ep' . $episodeNumber;
        }

        Roach::startSpider(AnimixPlaySpider::class, new Overrides(startUrls: $urls));

        return Command::SUCCESS;
    }
}
