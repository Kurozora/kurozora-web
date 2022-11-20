<?php

namespace App\Models;

//use App\Scopes\TvRatingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class AnimeStaff extends KModel implements Sitemapable
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'anime_staff';
    protected $table = self::TABLE_NAME;

    /**
     * Convert the model to its sitemap representation.
     *
     * @return Url|string|array
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('anime.staff', $this->anime))
            ->setChangeFrequency('weekly');
    }

    /**
     * The anime relationship of anime staff.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
//            ->withoutGlobalScope(new TvRatingScope());
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
}
