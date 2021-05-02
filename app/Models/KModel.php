<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class KModel extends Model
{
    use HasRelationships;

    // Remove column guards
    protected $guarded = [];

    /**
     * Generates a key to be used for caching
     *
     * @param array $options
     * @return string
     */
    static function cacheKey(array $options = []): string
    {
        // Start with the table name
        $key = get_called_class()::TABLE_NAME;

        // Add a name
        if (isset($options['name'])) $key .= '-' . $options['name'];

        // Add an ID
        if (isset($options['id'])) $key .= '-' . $options['id'];

        // Add a limit
        if (isset($options['limit'])) $key .= '-' . $options['limit'];

        // Add a where
        if (isset($options['where'])) $key .= '-' . implode(',', array_map('implode', $options['where']));

        // Add a whereBetween
        if (isset($options['whereBetween'])) $key .= '-' . implode(',', $options['whereBetween']);

        return $key;
    }
}
