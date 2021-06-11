<?php

namespace App\Services;

use App\Models\KDashboard\Song as KSong;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;

class ImportSongProcessor
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
            $song = Song::where([
                ['mal_id', $kSong->id],
            ])->first();

            if (empty($song)) {
                $songTitle = $kSong->getTitle();
                $songArtist = $kSong->getArtist();

                $duplicateSong = Song::where([
                    ['title', $songTitle],
                    ['artist', $songArtist],
                ])->first();

                if (!empty($songTitle) && empty($duplicateSong)) {
                    Song::create([
                        'mal_id' => $kSong->id,
                        'title' => $songTitle,
                        'artist' => $songArtist,
                    ]);
                }
            }
        }
    }
}
