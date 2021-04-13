<?php

namespace App\Traits;

use Nicolaslopezj\Searchable\SearchableTrait;

trait KuroSearchTrait {
    use SearchableTrait;

    /**
     * Custom Kurozora search function with added features
     *
     * @param $query
     * @param array $options
     * @return mixed
     */
    public static function kuroSearch($query, $options = []) {
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
