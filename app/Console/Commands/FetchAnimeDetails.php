<?php

namespace App\Console\Commands;

use App\Anime;
use App\AnimeGenre;
use App\Genre;
use Illuminate\Console\Command;
use musa11971\TVDB\TVDB;

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

        if($anime->fetched_details) {
            $this->error('The details were already fetched for this Anime.');
            return false;
        }

        // Start retrieving
        $this->info('Start retrieving anime details...');
        $this->info('');

        $details = TVDB::getSeries($anime->tvdb_id);

        // Synopsis
        $this->info('[Retrieving synopsis]');
        $anime->synopsis = $details->synopsis;
        $this->info('[Synopsis retrieved]');
        $this->info('');

        // Watch rating
        $this->info('[Retrieving watch rating]');
        $anime->watch_rating = 'TBA';
        $this->info('[Watch rating retrieved]');
        $this->info('');

        // Slug
        $this->info('[Retrieving slug]');
        $anime->slug = $details->slug;
        $this->info('[Slug retrieved]');
        $this->info('');

        // Runtime
        $this->info('[Retrieving runtime]');
        $anime->runtime = 0;
        $this->info('[Runtime retrieved]');
        $this->info('');

        // Network
        $this->info('[Retrieving network]');
        $anime->network = $details->network['name'];
        $this->info('[Network retrieved]');
        $this->info('');

        // IMDB ID
        $this->info('[Retrieving IMDB ID]');
        $anime->imdb_id = $details->imdbId;
        $this->info('[IMDB ID retrieved]');
        $this->info('');

        // Title
        $this->info('[Retrieving title]');
        $anime->title = $details->title;
        $this->info('[Title retrieved]');
        $this->info('');

        // Status
        $this->info('[Retrieving status]');
        $anime->status = $details->status;
        $this->info('[Status retrieved]');
        $this->info('');

        // Genres
        $this->info('[Retrieving genres]');

        foreach($details->genres as $genre) {
            // Get the appropriate genre
            $foundGenre = Genre::firstOrCreate(
                ['name' => $genre]
            );

            // Assign to this anime
            AnimeGenre::create([
                'anime_id' => $anime->id,
                'genre_id' => $foundGenre->id
            ]);
        }

        $this->info('[Genres retrieved]');
        $this->info('');

        // Save details
        $anime->fetched_details = true;
        $anime->save();

        $this->info('Finished fetching details');

        return true;
    }
}
