<?php

namespace App\Console\Commands;

use App\Anime;
use Illuminate\Console\Command;
use TVDB;

class FetchAnimeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animes:fetch_images {id : The ID of the Anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves the images for an anime and stores them';

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

        if($anime->fetched_images) {
            $this->error('The images were already fetched for this Anime.');
            return false;
        }

        // Start retrieving
        $this->info('Start retrieving image data...');
        $this->info('');

        $tvdb_handle = new TVDB();

        // Retrieve posters
        $this->info('[Retrieving posters]');
        $anime->cached_poster = $tvdb_handle->getAnimePoster($anime->tvdb_id, false);
        $anime->cached_poster_thumbnail = $tvdb_handle->getAnimePoster($anime->tvdb_id, true);
        $this->info('[Posters retrieved]');
        $this->info('');

        // Retrieve backgrounds
        $this->info('[Retrieving backgrounds]');
        $anime->cached_background = $tvdb_handle->getAnimeBackground($anime->tvdb_id, false);
        $anime->cached_background_thumbnail = $tvdb_handle->getAnimeBackground($anime->tvdb_id, true);
        $this->info('[Backgrounds retrieved]');
        $this->info('');

        // Save data
        $anime->fetched_images = true;
        $anime->save();
        $this->info('Images successfully saved.');

        return true;
    }
}
