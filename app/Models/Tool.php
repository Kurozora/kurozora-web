<?php

namespace App\Models;

use App\Traits\Model\HasSlug;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;

class Tool extends KModel
{
    use HasSlug,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'tools';
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
     * The anime made with the tool.
     *
     * @return MorphToMany
     */
    public function animes(): MorphToMany
    {
        return $this->morphedByMany(Anime::class, 'model', MediaTool::class)
            ->withTimestamps();
    }

    /**
     * The manga made with the tool.
     *
     * @return MorphToMany
     */
    public function mangas(): MorphToMany
    {
        return $this->morphedByMany(Manga::class, 'model', MediaTool::class)
            ->withTimestamps();
    }

    /**
     * The games made with the tool.
     *
     * @return MorphToMany
     */
    public function games(): MorphToMany
    {
        return $this->morphedByMany(Game::class, 'model', MediaTool::class)
            ->withTimestamps();
    }
}
