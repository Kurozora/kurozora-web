<?php

namespace App\Models;

use App\Enums\SongType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class MediaSong extends KModel implements Sitemapable
{
    use HasFactory,
        HasUlids,
        SoftDeletes,
        SortableTrait;

    // Table name
    const TABLE_NAME = 'media_songs';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    /**
     * The sortable configurations.
     *
     * @var array
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * The model relationship.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
//            ->withoutGlobalScope(new TvRatingScope());
    }

    /**
     * The song relationship.
     *
     * @return BelongsTo
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Get the song type attribute.
     *
     * @param int|null $value
     * @return SongType|null
     */
    public function getTypeAttribute(?int $value): ?SongType
    {
        return isset($value) ? SongType::fromValue($value) : null;
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return match ($this->model_type) {
            Anime::class => Url::create(route('anime.songs', $this->model))
                ->setChangeFrequency('weekly'),
            Game::class => Url::create(route('games.songs', $this->model))
                ->setChangeFrequency('weekly'),
            default => [],
        };
    }
}
