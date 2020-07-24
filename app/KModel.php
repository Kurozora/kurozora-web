<?php

namespace App;

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
    static function cacheKey($options = []) {
        // Start with the table name
        $key = get_called_class()::TABLE_NAME;

        // Add a name
        if(isset($options['name'])) $key .= '-' . $options['name'];

        // Add an ID
        if(isset($options['id'])) $key .= '-' . $options['id'];

        return $key;
    }
}
