<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;

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
//        if($anime->fetched_images) {
//            $this->error('The images were already fetched for this Anime.');
//            return 0;
//        }
//
//        // Start retrieving
//        $this->info('Start retrieving image data...');
//        $this->info('');
//
//        // Retrieve posters
//        $this->info('[Retrieving posters]');
//
//        try {
//            $posterData = TVDB::getSeriesImages($anime->tvdb_id, TVDB::IMAGE_TYPE_POSTER);
//        }
//        catch(TVDBNotFoundException $e) {
//            $this->info('[Posters not found]');
//        }
//
//        if(isset($posterData) && count($posterData)) {
//            $anime->cached_poster = $posterData[0]['image'];
//            $anime->cached_poster_thumbnail = $posterData[0]['image_thumb'];
//        }
//        else {
//            $anime->cached_poster = null;
//            $anime->cached_poster_thumbnail = null;
//        }
//        $this->info('[Posters retrieved]');
//        $this->info('');
//
//        // Retrieve backgrounds
//        $this->info('[Retrieving backgrounds]');
//
//        try {
//            $backgroundData = TVDB::getSeriesImages($anime->tvdb_id, TVDB::IMAGE_TYPE_FANART);
//        }
//        catch(TVDBNotFoundException $e) {
//            $this->info('[Backgrounds not found]');
//        }
//
//        if(isset($backgroundData) && count($backgroundData)) {
//            $anime->cached_background = $backgroundData[0]['image'];
//            $anime->cached_background_thumbnail = $backgroundData[0]['image_thumb'];
//        }
//        else {
//            $anime->cached_background = null;
//            $anime->cached_background_thumbnail = null;
//        }
//        $this->info('[Backgrounds retrieved]');
//        $this->info('');
//
//        // Save data
//        $anime->fetched_images = true;
//        $anime->save();
//        $this->info('Images successfully saved.');

        $this->warn('Fetching of anime images is currently disabled.');
        return 1;
    }
}
