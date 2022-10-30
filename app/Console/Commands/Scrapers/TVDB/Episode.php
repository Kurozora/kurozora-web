<?php

namespace App\Console\Commands\Scrapers\TVDB;

use App\Models\Anime;
use App\Spiders\TVDB\EpisodeSpider;
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
    protected $signature = 'scrape:tvdb_episode {tvdbID? : The id of the anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape episode data from TVDB for the given TVDB ID.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tvdbID = $this->argument('tvdbID');

        if (empty($tvdbID)) {
            $tvdbID = $this->ask('TVDB id');
        }

        if (empty($tvdbID)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        } else if (!is_numeric($tvdbID)) {
            $this->info('ID must be of a numeric value. Adios...');
            return Command::INVALID;
        }

        Roach::startSpider(EpisodeSpider::class, new Overrides(startUrls: [
            config('scraper.domains.tvdb.dereferrer.series') . '/' . $tvdbID,
        ]));

        $episodes = Anime::withoutGlobalScopes()
            ->firstWhere('tvdb_id', '=', $tvdbID)
            ->episodes()
            ->orderBy('number_total')
            ->get();

        foreach ($episodes as $key => $episode) {
            $nextEpisode = null;
            $previousEpisode = null;

            if ($key != count($episodes) - 1) {
                $nextEpisode = $episodes[$key + 1]->id;
            }

            if ($key != 0) {
                $previousEpisode = $episodes[$key - 1]->id;
            }

            $episode->update([
                'next_episode_id' => $nextEpisode,
                'previous_episode_id' => $previousEpisode,
            ]);
        }

        return Command::SUCCESS;
    }
}
