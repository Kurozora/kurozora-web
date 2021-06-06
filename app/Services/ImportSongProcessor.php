<?php

namespace App\Services;

use App\Models\KDashboard\Song as KSong;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;
use Str;

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

            $this->getTitle($kSong);

            if (empty($song)) {
                $songTitle = $this->getTitle($kSong);
                $songArtist = $this->getArtist($kSong);

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

    /**
     * The title of the song.
     *
     * @param KSong $kSong
     * @return ?string
     */
    protected function getTitle(KSong $kSong): ?string
    {
        $song = $kSong->song;
        $matchCount = preg_match('/"([^"]+)"/', $song, $songTitleMatches);
        $songTitle = null;
        if ($matchCount) {
            $songTitle = trim($songTitleMatches[1]);
        }
        return $songTitle;
    }

    /**
     * The artist of the song.
     *
     * @param KSong $kSong
     * @return string|null
     */
    protected function getArtist(KSong $kSong): ?string
    {
        $song = $kSong->song;
        $matchCount = preg_match('/" by.*/i', $song, $songArtistMatches);
        $songArtist = null;
        if ($matchCount) {
            $songArtist = $this->cleanSongArtist($songArtistMatches[0]);
        }
        return $songArtist;
    }

    /**
     * Returns a cleaned version of the given artist string.
     *
     * @param string $uncleanSongArtist
     * @return string
     */
    protected function cleanSongArtist(string $uncleanSongArtist): string
    {
        $cleanSongArtist = $uncleanSongArtist;
        $searchRegex = [
            '/" by /i',
            '/\(eps .*\)/i',
            '/\(ep .*\)/i',
            '/\(episode.*\)/i',
        ];

        foreach ($searchRegex as $regex) {
            $cleanSongArtist = preg_replace($regex, '', $cleanSongArtist);
        }

        return trim($cleanSongArtist);
    }
}
