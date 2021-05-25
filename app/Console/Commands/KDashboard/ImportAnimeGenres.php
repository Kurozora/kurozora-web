<?php

namespace App\Console\Commands\KDashboard;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Models\MediaGenre;
use Illuminate\Console\Command;

class ImportAnimeGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime genres from the KDashboard database.';

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
    public function handle(): int
    {
        $kMediaGenres = KMediaGenre::where('type', 'anime')->get();
        $oldCount = MediaGenre::count('id');
        $this->info('Total old anime genres: ' . $oldCount);

        $this->withProgressBar($kMediaGenres, function (KMediaGenre $kMediaGenre) {
            $anime = Anime::firstWhere('mal_id', $kMediaGenre->media_id);
            $genre = Genre::firstWhere([
                ['name', $kMediaGenre->genre->genre],
            ]);

            $mediaGenre = MediaGenre::where([
                ['type', 'anime'],
                ['media_id', $anime->id],
                ['genre_id', $genre->id],
            ])->first();

            if ($mediaGenre) {
                return;
            }

            MediaGenre::create([
                'media_id' => $anime->id,
                'genre_id' => $genre->id,
                'type' => 'anime',
            ]);
        });

        $newCount = MediaGenre::count('id');

        $this->newLine();
        $this->info('Total new anime genres added: ' . $newCount - $oldCount);
        $this->info('Total anime genres: ' . $newCount);

        return 1;
    }
}
