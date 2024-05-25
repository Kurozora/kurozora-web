<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class MediaStaff extends KModel implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'media_staff';
    protected $table = self::TABLE_NAME;

    /**
     * The anime relationship of anime staff.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
//            ->withoutTvRatings();
    }

    /**
     * The person relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * The staff role relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function staff_role(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class);
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return match ($this->model_type) {
            Anime::class => Url::create(route('anime.staff', $this->model))
                ->setChangeFrequency('weekly')
                ->setLastModificationDate($this->model->updated_at),
            Manga::class => Url::create(route('manga.staff', $this->model))
                ->setChangeFrequency('weekly')
                ->setLastModificationDate($this->model->updated_at),
            default => [],
        };
    }
}
