<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimeSeason extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'anime_seasons';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the episodes associated with the season
     *
     * @return HasMany
     */
    function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'season_id');
    }

    /**
     * Returns the Anime that owns the season
     *
     * @return BelongsTo
     */
    function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the title of the Season
     *
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->number == 0)
            return 'Specials';

        if ($this->title != null)
            return $this->title;

        return 'Season ' . $this->number;
    }

    /**
     * Gets the first aired date of the first aired episode in this season.
     *
     * @return ?string
     */
    public function getFirstAired(): ?string
    {
        /** @var ?Episode $firstEpisode */
        $firstEpisode = $this->episodes->firstWhere('number', 1);

        if ($firstEpisode == null)
            return null;

        return $firstEpisode->first_aired->format('Y-m-d');
    }

    /**
     * Gets the count of the amount of episodes in this season
     *
     * @return int
     */
    public function getEpisodeCount(): int
    {
        return Episode::where('season_id', $this->id)->count();
    }

    /**
     * Gets the episodes for this season
     *
     * @return Episode[]|array
     */
    public function getEpisodes(): Collection|array
    {
        return Episode::where('season_id', $this->id)->get();
    }
}
