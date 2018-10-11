<?php

namespace App\Console\Commands;

use App\Anime;
use App\AnimeEpisode;
use App\AnimeSeason;
use Carbon\Carbon;
use Illuminate\Console\Command;
use TVDB;

class FetchBaseAnimeEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animes:fetch_base_episodes {id : The ID of the Anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves the base episodes for an Anime.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Request anime ID parameter
        $animeID = $this->argument('id');

        $anime = Anime::find($animeID);

        // Specified Anime does not exists
        if($anime == null) {
            $this->error('The Anime was not found.');
            return false;
        }

        if($anime->fetched_base_episodes) {
            $this->error('The base episodes were already fetched for this Anime.');
            return false;
        }

        // TVDB handle
        $tvdb = new TVDB();

        // Start looping through episode requests
        $resultSet = [true];
        $totalInsertCount = 0;
        $highestSeason = 0;
        $pageCount = 1;

        $this->info('Start retrieving episode data.');

        while(!empty($resultSet)) {
            $this->info('Checking page ' . $pageCount);
            $resultSet = $tvdb->getAnimeEpisodeData($anime->tvdb_id, $pageCount);

            // Last page
            if($resultSet == null || $resultSet < 1) {
                $this->info('No more episodes found on page ' . $pageCount);
                break;
            }

            $this->info('Found ' . count($resultSet) . ' episodes on page ' . $pageCount);

            // Loop through the found episodes and insert them if they are not in the DB yet
            $insertCount = 0;

            foreach($resultSet as $episodeResult) {
                // Already exists
                $checkFindEpisode = AnimeEpisode::where([
                    ['anime_id', '=', $anime->id],
                    ['number', '=', $episodeResult->airedEpisodeNumber],
                    ['season', '=', $episodeResult->airedSeason]
                ])->first();

                if($checkFindEpisode)
                    continue;

                // Insert the new episode
                $firstAiredValue = null;

                if($episodeResult->firstAired != null)
                    $firstAiredValue = new Carbon($episodeResult->firstAired);

                AnimeEpisode::create([
                    'anime_id'      => $anime->id,
                    'name'          => $episodeResult->episodeName,
                    'season'        => $episodeResult->airedSeason,
                    'number'        => $episodeResult->airedEpisodeNumber,
                    'overview'      => $episodeResult->overview,
                    'first_aired'   => $firstAiredValue
                ]);

                $insertCount++;

                // Update the highest season
                if($episodeResult->airedSeason > $highestSeason)
                    $highestSeason = $episodeResult->airedSeason;
            }

            $this->info($insertCount . '/' . count($resultSet) . ' episodes inserted!');

            $totalInsertCount += $insertCount;

            // Ready for the next iteration (page)
            $this->info('');
            $pageCount++;
        }

        $this->info('All done! ' . $totalInsertCount . ' total of episodes inserted.');

        // Create the seasons
        for($i = 0; $i <= $highestSeason; $i++) {
            // Already exists
            $checkFindSeason = AnimeSeason::where([
                ['anime_id', '=', $anime->id],
                ['number', '=', $i]
            ])->first();

            if($checkFindSeason)
                continue;

            // Create the season
            AnimeSeason::create([
                'number'    => $i,
                'anime_id'  => $anime->id
            ]);
        }

        // Update the Anime variables
        $anime->episode_count = $totalInsertCount;
        $anime->season_count = $highestSeason;
        $anime->fetched_base_episodes = true;
        $anime->save();
    }
}
