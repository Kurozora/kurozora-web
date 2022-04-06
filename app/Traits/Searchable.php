<?php

namespace App\Traits;

use App\Models\Anime;
use App\Models\Character;
use App\Models\User;
use App\Scopes\TvRatingScope;
use Illuminate\Database\Eloquent\Builder;
use Nicolaslopezj\Searchable\SearchableTrait;

trait Searchable {
    use SearchableTrait;

    /**
     * Custom Kurozora search function with added features
     *
     * @param $query
     * @return array|Anime|Builder|Character|User
     */
    public static function kSearch($query): array|Anime|Builder|Character|User
    {
        // Find the item by ID if the search query is an ID
        preg_match('/^id:\s*([0-9]+?)$/i', $query, $idMatches);

        // If searching with ID
        if (isset($idMatches[1])) {
            if (self::class === Anime::class) {
                return self::withoutGlobalScope(new TvRatingScope)
                    ->withTvRating()
                    ->where('id', $idMatches[1]);
            }
            return self::where('id', $idMatches[1]);
        }

        // If not searching with ID
        if (self::class === Anime::class) {
            return self::withoutGlobalScope(new TvRatingScope)
                ->withTvRating()
                ->search($query, null, true, true);
        }

        return self::search($query, null, true, true);
    }
}
