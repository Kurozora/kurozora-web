<?php

namespace App\Console\Commands;

use App\Enums\AnimeStatus;
use App\Enums\DayOfWeek;
use App\Models\Anime;
use App\Models\AnimeGenre;
use App\Models\Genre;
use Carbon\Carbon;
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
     * @return int
     *
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
            return 0;
        }

        if($anime->tvdb_id === null) {
            $this->error('The Anime does not have a connected TVDB ID.');
            return 0;
        }

        if($anime->fetched_details) {
            $this->error('The details were already fetched for this Anime.');
            return 0;
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
        $anime->watch_rating = $details->watchRating;
        $this->info('[Watch rating retrieved]');
        $this->info('');

        // Slug
        $this->info('[Retrieving slug]');
        $anime->slug = $details->slug;
        $this->info('[Slug retrieved]');
        $this->info('');

        // Runtime
        $this->info('[Retrieving runtime]');
        $anime->runtime = $details->runtime;
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
        $anime->title = ($details->title !== null) ? $details->title : $anime->title;
        $this->info('[Title retrieved]');
        $this->info('');

        // Status
        $this->info('[Retrieving status]');
        $anime->status = (AnimeStatus::hasKey($details->status)) ? AnimeStatus::getValue($details->status) : AnimeStatus::TBA;
        $this->info('[Status retrieved]');
        $this->info('');

        // First aired
        $this->info('[Retrieving first air date]');
        $anime->first_aired = ($details->firstAired);
        $this->info('[First air date retrieved]');
        $this->info('');

        // Air time
        $this->info('[Retrieving air time]');
        $anime->air_time = (Carbon::parse($details->airs["time"]));
        $this->info('[Air time retrieved]');
        $this->info('');

        // Air day
        $this->info('[Retrieving air day]');
        $anime->air_day = (DayOfWeek::getValue($details->airs["dayOfWeek"]));
        $this->info('[Air day retrieved]');
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

        return 1;
    }
}
