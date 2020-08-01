<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExplorePageCategory extends KModel
{
    // Table name
    const TABLE_NAME = 'explore_page_categories';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the animes associated with the category.
     *
     * @return BelongsToMany
     */
    function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, ExplorePageCategoryAnime::class, 'explore_page_category_id', 'anime_id');
    }

    /**
     * Returns the genres associated with the category.
     *
     * @return BelongsToMany
     */
    function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, ExplorePageCategoryGenre::class, 'explore_page_category_id', 'genre_id');
    }
}
