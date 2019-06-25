<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExplorePageCategory extends Model
{
    // Table name
    const TABLE_NAME = 'explore_page_categories';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the animes associated with the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function animes() {
        return $this->belongsToMany(Anime::class, ExplorePageCategoryAnime::class, 'explore_page_category_id', 'anime_id');
    }

    /**
     * Returns the genres associated with the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function genres() {
        return $this->belongsToMany(Genre::class, ExplorePageCategoryGenre::class, 'explore_page_category_id', 'genre_id');
    }
}
