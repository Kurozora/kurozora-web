<?php

namespace App\Models\KDashboard;

use App\Enums\SongType;
use App\Models\KDashboard\Song as KSong;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    // Table name
    const TABLE_NAME = 'song';
    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'unique_id';

    /**
     * Returns the anime the song belongs to.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'anime_id', 'id');
    }

    /**
     * Returns a humanly readable type attribute.
     *
     * @param ?int $type
     *
     * @return ?SongType
     */
    public function getTypeAttribute(?int $type): ?SongType
    {
        return $type ? SongType::fromValue($type) : null;
    }

    /**
     * The title of the song.
     *
     * @return ?string
     */
    public function getTitle(): ?string
    {
        $song = $this->song;
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
     * @return string|null
     */
    public function getArtist(): ?string
    {
        $song = $this->song;
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
            '/\(ep .*\)/i',
            '/\(eps .*\)/i',
            '/\(episode .*\)/i',
            '/\(episodes .*\)/i',
        ];

        foreach ($searchRegex as $regex) {
            $cleanSongArtist = preg_replace($regex, '', $cleanSongArtist);
        }

        return trim($cleanSongArtist);
    }

    /**
     * Returns the type of the song.
     *
     * @return int|null
     */
    public function getSongType(): ?int
    {
        try {
            return SongType::fromValue($this->type)->value;
        } catch (InvalidEnumKeyException $enumKeyException) {
            return null;
        }
    }

    /**
     * Returns the position of the song. For example Opening "1", Ending "3", etc.
     *
     * @return int
     */
    public function getSongPosition(): int
    {
        $song = $this->song;
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
     * @return string|null
     */
    public function getSongEpisodes(): ?string
    {
        $song = $this->song;
        $episodes = null;
        $searchRegex = [
            '/\(ep .*\)/i',
            '/\(eps .*\)/i',
            '/\(episode .*\)/i',
            '/\(episodes .*\)/i',
        ];

        foreach ($searchRegex as $regex) {
            $matchCount = preg_match($regex, $song, $episodes);

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
