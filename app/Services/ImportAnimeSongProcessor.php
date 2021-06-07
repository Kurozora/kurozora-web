<?php

namespace App\Services;

use App\Enums\SongType;
use App\Models\Anime;
use App\Models\AnimeSong;
use App\Models\KDashboard\Song as KSong;
use App\Models\Song;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
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
            $anime = Anime::where([
                ['mal_id', $kSong->anime_id],
            ])->first();
            $song = Song::where([
                ['mal_id', $kSong->id],
            ])->first();
            $animeSong = AnimeSong::where([
                ['anime_id', $anime->id],
                ['song_id', $song?->id],
            ])->first();

            if (empty($animeSong) && !empty($song)) {
                AnimeSong::create([
                    'anime_id' => $anime->id,
                    'song_id' => $song->id,
                    'type' => $this->getSongType($kSong),
                    'position' => $this->getSongPosition($kSong),
                    'episodes' => $this->getSongEpisodes($kSong),
                ]);
            }
        }
    }

    /**
     * Returns the type of the song.
     *
     * @param KSong $kSong
     * @return int|null
     */
    protected function getSongType(KSong $kSong): ?int
    {
        try {
            return SongType::fromValue($kSong->type)->value;
        } catch (InvalidEnumKeyException $enumKeyException) {
            return null;
        }
    }

    /**
     * Returns the position of the song. For example Opening "1", Ending "3", etc.
     *
     * @param KSong $kSong
     * @return int
     */
    protected function getSongPosition(KSong $kSong): int
    {
        $song = $kSong->song;
        $matchCount = preg_match('/#.+?[^\d]/i', $song, $songPosition);

        if ($matchCount) {
            $cleanSongPosition = preg_replace('/#[A-Z]+|#|[A-Z]+:|:/i', '', $songPosition);
            return (int) $cleanSongPosition[0];
        }

        return 1;
    }

    /**
     * Returns the episodes the song was played in.
     *
     * @param KSong $kSong
     * @return string|null
     */
    protected function getSongEpisodes(KSong $kSong): ?string
    {
        $songString = $kSong->song;
        $episodes = null;
        $searchRegex = [
            '/\(ep .*\)/i',
            '/\(eps .*\)/i',
            '/\(episode .*\)/i',
            '/\(episodes .*\)/i',
        ];

        foreach ($searchRegex as $regex) {
            $matchCount = preg_match($regex, $songString, $episodes);

            if ($matchCount) {
                $episodes = $this->cleanSongEpisodeString($episodes[0]);
                break;
            }
        }

        return !empty($episodes) ? $episodes : null;
    }

    /**
     * Returns a cleaned version of the given episode string.
     *
     * @param string $uncleanEpisodeString
     * @return string
     */
    protected function cleanSongEpisodeString(string $uncleanEpisodeString): string
    {
        $cleanEpisodeString = $uncleanEpisodeString;
        $searchRegex = [
            '/\(eps[^ ]*|\)/i',
            '/\(ep[^ ]*|\)/i',
            '/\(episode[^ ]*|\)/i',
            '/\(episodes[^ ]*|\)/i',
        ];

        foreach ($searchRegex as $regex) {
            $cleanEpisodeString = preg_replace($regex, '', $cleanEpisodeString);
        }

        return trim($cleanEpisodeString);
    }
}
