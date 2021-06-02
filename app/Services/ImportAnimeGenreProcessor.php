<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Models\MediaGenre;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeGenreProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMediaGenre[] $kMediaGenres
     * @return void
     */
    public function process(Collection|array $kMediaGenres)
    {
        foreach ($kMediaGenres as $kMediaGenre) {
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
        }
    }
}
