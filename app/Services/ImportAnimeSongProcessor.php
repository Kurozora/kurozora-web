<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeSong;
use App\Models\KDashboard\Song as KSong;
use App\Models\Song;
use App\Scopes\TvRatingScope;
use Illuminate\Database\Eloquent\Collection;

class ImportAnimeSongProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KSong[] $kSongs
     * @return void
     */
    public function process(Collection|array $kSongs)
    {
        foreach ($kSongs as $kSong) {
            $anime = Anime::withoutGlobalScope(new TvRatingScope)->where([
                ['mal_id', $kSong->anime_id],
            ])->first();
            $song = Song::where([
                ['mal_id', $kSong->id],
            ])->first();

            if (empty($song)) {
                $songTitle = $kSong->getTitle();
                $songArtist = $kSong->getArtist();

                $song = Song::where([
                    ['title', $songTitle],
                    ['artist', $songArtist],
                ])->first();
            }

            $animeSong = AnimeSong::where([
                ['anime_id', $anime->id],
                ['song_id', $song?->id],
            ])->first();

            if (empty($animeSong) && !empty($song)) {
                AnimeSong::create([
                    'anime_id' => $anime->id,
                    'song_id' => $song->id,
                    'type' => $kSong->getSongType(),
                    'position' => $kSong->getSongPosition(),
                    'episodes' => $kSong->getSongEpisodes(),
                ]);
            }
        }
    }
}
