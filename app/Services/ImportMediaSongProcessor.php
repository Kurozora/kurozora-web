<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\KDashboard\Song as KSong;
use App\Models\MediaSong;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaSongProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KSong[] $kSongs
     * @return void
     */
    public function process(Collection|array $kSongs): void
    {
        foreach ($kSongs as $kSong) {
            $model = Anime::withoutGlobalScopes()
                ->where([
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

            $mediaSong = MediaSong::where([
                ['model_type', $model->getMorphClass()],
                ['model_id', $model->id],
                ['song_id', $song?->id],
            ])->first();

            if (empty($mediaSong) && !empty($song)) {
                MediaSong::create([
                    'model_type' => $model->getMorphClass(),
                    'model_id' => $model->id,
                    'song_id' => $song->id,
                    'type' => $kSong->getSongType(),
                    'position' => $kSong->getSongPosition(),
                    'episodes' => $kSong->getSongEpisodes(),
                ]);
            }
        }
    }
}
