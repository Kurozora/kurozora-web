<?php

namespace App\Console\Commands;

use App\Actor;
use App\Anime;
use Carbon\Carbon;
use Illuminate\Console\Command;
use musa11971\TVDB\TVDB;

class FetchAnimeActors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animes:fetch_actors {id : The ID of the Anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves and saves the actors for the Anime from TVDB';

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
     * @throws \musa11971\TVDB\Exceptions\TVDBNotFoundException
     * @throws \musa11971\TVDB\Exceptions\TVDBUnauthorizedException
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

        if($anime->fetched_actors) {
            $this->error('The actors were already fetched for this Anime.');
            return false;
        }

        // Start retrieving
        $this->info('Start retrieving anime actors...');
        $this->info('');

        // Get the actors
        $retActors = TVDB::getSeriesActors($anime->tvdb_id);

        // Delete old actors if there were any
        Actor::where('anime_id', $anime->id)->delete();

        // Insert the new actors
        $insertActors = [];

        foreach ($retActors as $actor) {
            $insertActors[] = [
                'created_at'    => Carbon::now(),
                'anime_id'      => $anime->id,
                'name'          => $actor->name,
                'role'          => $actor->role,
                'image'         => $actor->imageURL
            ];
        }

        Actor::insert($insertActors);

        $anime->fetched_actors = true;
        $anime->save();

        $this->info('Finished fetching actors - ' . count($insertActors) . ' found.');

        return true;
    }
}
