<?php

namespace App\Models;

use App\Traits\Model\HasSlug;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;

class Franchise extends KModel
{
    use HasSlug,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'franchises';
    protected $table = self::TABLE_NAME;

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * The anime belonging to the franchise.
     *
     * @return MorphToMany
     */
    public function animes(): MorphToMany
    {
        return $this->morphedByMany(Anime::class, 'model', MediaFranchise::class)
            ->withTimestamps();
    }

    /**
     * The manga belonging to the franchise.
     *
     * @return MorphToMany
     */
    public function mangas(): MorphToMany
    {
        return $this->morphedByMany(Manga::class, 'model', MediaFranchise::class)
            ->withTimestamps();
    }

    /**
     * The games belonging to the franchise.
     *
     * @return MorphToMany
     */
    public function games(): MorphToMany
    {
        return $this->morphedByMany(Game::class, 'model', MediaFranchise::class)
            ->withTimestamps();
    }
}
