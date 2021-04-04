<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\AnimeEpisode;
use App\Models\AnimeSeason;
use Illuminate\Console\Command;

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
     * @return int
     */
    public function handle()
    {
//        // Request anime ID parameter
//        $animeID = $this->argument('id');
//
//        $anime = Anime::find($animeID);
//
//        // Specified Anime does not exists
//        if($anime == null) {
//            $this->error('The Anime was not found.');
//            return 0;
//        }
//
//        if($anime->tvdb_id === null) {
//            $this->error('The Anime does not have a connected TVDB ID.');
//            return 0;
//        }
//
//        if($anime->fetched_base_episodes) {
//            $this->error('The base episodes were already fetched for this Anime.');
//            return 0;
//        }
//
//        // Start looping through episode requests
//        $totalInsertCount = 0;
//        $highestSeason = 0;
//        $page = 1;
//
//        $this->info('Start retrieving episode data.');
//
//        do {
//            $this->info('Checking page ' . $page);
//
//            try {
//                $resultSet = TVDB::getSeriesEpisodes($anime->tvdb_id, $page);
//            }
//            catch(TVDBNotFoundException $e) {
//                $this->info('No more episodes found on page ' . $page);
//                $anime->fetched_base_episodes = true;
//                $anime->save();
//                die;
//            }
//
//            $this->info('Found ' . count($resultSet) . ' episodes on page ' . $page);
//
//            // Loop through the found episodes and insert them if they are not in the DB yet
//            $insertCount = 0;
//
//            foreach($resultSet as $episodeResult) {
//                // Get the appropriate season
//                $season = AnimeSeason::firstOrCreate(
//                    ['anime_id' => $anime->id],
//                    ['number' => $episodeResult->season]
//                );
//
//                // This episode already exists
//                if(AnimeEpisode::where([
//                    ['number',      '=', $episodeResult->number],
//                    ['season_id',   '=', $season->id]
//                ])->exists())
//                    continue;
//
//                // Insert the new episode
//                $insertData = [
//                    'title'         => $episodeResult->name,
//                    'season_id'     => $season->id,
//                    'number'        => $episodeResult->number,
//                    'overview'      => $episodeResult->synopsis,
//                    'first_aired'   => $episodeResult->firstAired,
//                    'image'         => $episodeResult->image
//                ];
//
//                AnimeEpisode::create($insertData);
//
//                $insertCount++;
//
//                // Update the highest season
//                if($episodeResult->season > $highestSeason)
//                    $highestSeason = $episodeResult->season;
//            }
//
//            $this->info($insertCount . '/' . count($resultSet) . ' episodes inserted!');
//
//            $totalInsertCount += $insertCount;
//
//            // Ready for the next iteration (page)
//            $this->info('');
//            $page++;
//        } while($resultSet->hasNextPage());
//
//        $this->info('All done! ' . $totalInsertCount . ' total of episodes inserted.');
//
//        // Update the Anime variables
//        $anime->episode_count = $totalInsertCount;
//        $anime->season_count = $highestSeason;
//        $anime->fetched_base_episodes = true;
//        $anime->save();

        $this->warn('Fetching of anime episodes is currently disabled.');
        return 1;
    }
}
