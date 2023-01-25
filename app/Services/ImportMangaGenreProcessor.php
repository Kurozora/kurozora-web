<?php

namespace App\Services;

use App\Models\Manga;
use App\Models\Genre;
use App\Models\KDashboard\MediaGenre as KMediaGenre;
use App\Models\MediaGenre;
use App\Models\MediaTheme;
use App\Models\Theme;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ImportMangaGenreProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KMediaGenre[] $kMediaGenres
     * @return void
     */
    public function process(Collection|array $kMediaGenres): void
    {
        foreach ($kMediaGenres as $kMediaGenre) {
            try {
                $manga = Manga::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kMediaGenre->media_id);
                $genre = Genre::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kMediaGenre->genre_id);
                $theme = Theme::withoutGlobalScopes()
                    ->firstWhere('mal_id', $kMediaGenre->genre_id);

                if (!$manga) {
                    info('Manga not found: ' . $kMediaGenre->media_id);
                    return;
                }

                if ($genre) {
                    $mediaGenre = MediaGenre::firstWhere([
                        ['model_type', Manga::class],
                        ['model_id', $manga->id],
                        ['genre_id', $genre->id],
                    ]);

                    if (empty($mediaGenre)) {
                        MediaGenre::create([
                            'model_type' => Manga::class,
                            'model_id' => $manga->id,
                            'genre_id' => $genre->id,
                        ]);
                    }
                } else if ($theme) {
                    $mediaTheme = MediaTheme::firstWhere([
                        ['model_type', Manga::class],
                        ['model_id', $manga->id],
                        ['theme_id', $theme->id],
                    ]);

                    if (empty($mediaTheme)) {
                        MediaTheme::create([
                            'model_type' => Manga::class,
                            'model_id' => $manga->id,
                            'theme_id' => $theme->id,
                        ]);
                    }
                }
            } catch (Exception $e) {
                if (empty($kMediaGenre)) {
                    info('Missing genre:' . $kMediaGenre->genre_id);
                } else {
                    logger()->error($e->getMessage());
                }
            }
        }
    }
}
