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
     * Finds or creates an Anime season
     *
     * @param $animeID
     * @param $seasonNumber
     * @return AnimeSeason
     */
    protected function findOrCreateSeason($animeID, $seasonNumber) {
        // Find the season
        $checkFindSeason = AnimeSeason::where([
            ['anime_id',    '=', $animeID],
            ['number',      '=', $seasonNumber]
        ])->first();

        // Create the season (it did not exist yet)
        if(!$checkFindSeason) {
            $checkFindSeason = AnimeSeason::create([
                'anime_id'  => $animeID,
                'number'    => $seasonNumber
            ]);
        }

        return $checkFindSeason;
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
            if($resultSet == null || count($resultSet) < 1) {
                $this->info('No more episodes found on page ' . $pageCount);
                break;
            }

            $this->info('Found ' . count($resultSet) . ' episodes on page ' . $pageCount);

            // Loop through the found episodes and insert them if they are not in the DB yet
            $insertCount = 0;

            foreach($resultSet as $episodeResult) {
                // Get the appropriate season
                $season = $this->findOrCreateSeason($anime->id, $episodeResult->airedSeason);

                // This episode already exists
                if(AnimeEpisode::where([
                    ['number',      '=', $episodeResult->airedEpisodeNumber],
                    ['season_id',   '=', $season->id]
                ])->exists())
                    continue;

                // Insert the new episode
                $firstAiredValue = null;

                if($episodeResult->firstAired != null && strlen($firstAiredValue)) {
                    $firstAiredValue = Carbon::parse($episodeResult->firstAired);
                    $firstAiredValue = $firstAiredValue->toDateTimeString();
                }

                $insertData = [
                    'name'          => $episodeResult->episodeName,
                    'season_id'     => $season->id,
                    'number'        => $episodeResult->airedEpisodeNumber,
                    'overview'      => $episodeResult->overview,
                    'first_aired'   => $firstAiredValue
                ];

                AnimeEpisode::create($insertData);

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

        // Update the Anime variables
        $anime->episode_count = $totalInsertCount;
        $anime->season_count = $highestSeason;
        $anime->fetched_base_episodes = true;
        $anime->save();
    }
}
