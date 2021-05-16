<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Nicolaslopezj\Searchable\SearchableTrait;

trait Searchable {
    use SearchableTrait;

    /**
     * Custom Kurozora search function with added features
     *
     * @param $query
     * @param array $options
     * @return array|Collection
     */
    public static function kSearch($query, array $options = []): array|Collection
    {
        // Set the limit
        $limit = (isset($options['limit'])) ? $options['limit'] : 10;

        // Find the item by ID if the search query is an ID
        preg_match('/^id:\s*([0-9]+?)$/i', $query, $idMatches);

        if (isset($idMatches[1])) {
            $foundEntity = self::find($idMatches[1]);

            return [$foundEntity];
        }

        return self::search($query)->limit($limit)->get();
    }
}
