<?php

namespace App\Console\Commands\Scrapers\TVDB;

use App\Models\Anime;
use App\Spiders\TVDB\EpisodeSpider;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Episode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:tvdb_episode {tvdbID? : The id of the anime. Accepts an array of comma separated IDs}';

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $tvdbIDs = $this->argument('tvdbID');

        if (empty($tvdbIDs)) {
            $tvdbIDs = $this->ask('TVDB id');
        }

        $tvdbIDs = explode(',', $tvdbIDs);

        if (empty($tvdbIDs)) {
            $this->info('ID is empty. Exiting...');

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::INVALID;
        }

        // Generate URLs
        $urls = [];
        foreach ($tvdbIDs as $tvdbID) {
            $urls[] = config('scraper.domains.tvdb.dereferrer.series') . '/' . $tvdbID;
        }

        // Scrape
        Roach::startSpider(EpisodeSpider::class, new Overrides(startUrls: $urls));

        // Post-process episodes
        foreach ($tvdbIDs as $tvdbID) {
            $anime = Anime::withoutGlobalScopes()
                ->firstWhere('tvdb_id', '=', $tvdbID);
            $episodes = $anime
                ->episodes()
                ->with(['mediaStat', 'translations'])
                ->orderBy('number_total')
                ->get();

            // Update anime season and episode count
            $anime->update([
                'season_count' => $anime->seasons()->count(),
                'episode_count' => $episodes->count()
            ]);

            // Chain episodes
            $episodeIDs = [];
            $nextCases = '';
            $previousCases = '';

            foreach ($episodes as $key => $episode) {
                $nextEpisode = null;
                $previousEpisode = null;

                if ($key != count($episodes) - 1) {
                    $nextEpisode = $episodes[$key + 1]->id;
                }

                if ($key != 0) {
                    $previousEpisode = $episodes[$key - 1]->id;
                }

                $episodeIDs[] = (int) $episode->id;
                $nextCases .= ' WHEN ' . (int) $episode->id . ' THEN ' . ($nextEpisode === null ? 'NULL' : (int) $nextEpisode);
                $previousCases .= ' WHEN ' . (int) $episode->id . ' THEN ' . ($previousEpisode === null ? 'NULL' : (int) $previousEpisode);
            }

            if (!empty($episodeIDs)) {
                \App\Models\Episode::withoutGlobalScopes()
                    ->whereIn('id', $episodeIDs)
                    ->update([
                        'next_episode_id' => DB::raw('CASE id' . $nextCases . ' END'),
                        'previous_episode_id' => DB::raw('CASE id' . $previousCases . ' END'),
                    ]);
            }
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
