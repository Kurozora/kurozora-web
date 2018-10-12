<?php

namespace App\Console\Commands;

use App\Anime;
use Illuminate\Console\Command;
use TVDB;

class FetchAnimeDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animes:fetch_details {id : The ID of the Anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves and saves the details for the Anime from TVDB';

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

        if($anime->fetched_details) {
            $this->error('The details were already fetched for this Anime.');
            return false;
        }

        // Start retrieving
        $this->info('Start retrieving anime details...');
        $this->info('');

        $tvdb_handle = new TVDB();

        // Synopsis
        $this->info('[Retrieving synopsis]');
        $anime->synopsis = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'synopsis');
        $this->info('[Synopsis retrieved]');
        $this->info('');

        // Watch rating
        $this->info('[Retrieving watch rating]');
        $anime->watch_rating = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'watch_rating');
        $this->info('[Watch rating retrieved]');
        $this->info('');

        // Slug
        $this->info('[Retrieving slug]');
        $anime->slug = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'slug');
        $this->info('[Slug retrieved]');
        $this->info('');

        // Runtime
        $this->info('[Retrieving runtime]');
        $retRuntime = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'runtime_minutes');

        if(is_numeric($retRuntime))
            $anime->runtime = (int) $retRuntime;

        $this->info('[Runtime retrieved]');
        $this->info('');

        // Network
        $this->info('[Retrieving network]');
        $anime->network = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'network');
        $this->info('[Network retrieved]');
        $this->info('');

        // IMDB ID
        $this->info('[Retrieving IMDB ID]');
        $anime->imdb_id = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'imdb_id');
        $this->info('[IMDB ID retrieved]');
        $this->info('');

        // Title
        $this->info('[Retrieving title]');
        $title = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'title');

        if($title != null)
            $anime->title = $title;

        $this->info('[Title retrieved]');
        $this->info('');

        // Status
        $this->info('[Retrieving status]');
        $status = $tvdb_handle->getAnimeDetailValue($anime->tvdb_id, 'status');

        if($status != null)
            $anime->status = $status;

        $this->info('[Status retrieved]');
        $this->info('');

        // Save details
        $anime->fetched_details = true;
        $anime->save();

        $this->info('Finished fetching details');

        return true;
    }
}
