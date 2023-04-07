<?php

namespace App\Traits;

trait SearchFilterable {
    /**
     * The array of filterable properties.
     *
     * @return array
     */
    public static function searchFilters(): array
    {
        return config('scout.meilisearch.index-settings.'. self::TABLE_NAME . '.filterableAttributes');
    }
}
