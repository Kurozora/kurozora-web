<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Genre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Models\MediaGenre;
use App\Models\MediaTheme;
use App\Models\Theme;
use App\Scopes\TvRatingScope;
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
            $anime = Anime::withoutGlobalScope(new TvRatingScope)->firstWhere('mal_id', $kMediaGenre->media_id);
            $genre = Genre::withoutGlobalScope(new TvRatingScope)->firstWhere([
                ['name', $kMediaGenre->genre->genre],
            ]);
            $theme = Theme::withoutGlobalScope(new TvRatingScope)->firstWhere([
                ['name', $kMediaGenre->genre->genre],
            ]);

            if ($genre) {
                $mediaGenre = MediaGenre::firstWhere([
                    ['model_type', Anime::class],
                    ['model_id', $anime->id],
                    ['genre_id', $genre->id],
                ]);

                if (empty($mediaGenre)) {
                    MediaGenre::create([
                        'model_type' => Anime::class,
                        'model_id' => $anime->id,
                        'genre_id' => $genre->id,
                    ]);
                }
            } else if ($theme) {
                $mediaTheme = MediaTheme::firstWhere([
                   ['nam', $kMediaGenre->genre->genre]
                ]);

                if (empty($mediaTheme)) {
                    MediaTheme::create([
                        'model_type' => Anime::class,
                        'model_id' => $anime->id,
                        'theme_id' => $theme->id,
                    ]);
                }
            }


        }
    }
}
