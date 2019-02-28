<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed id
 * @property mixed name
 */
class AppTheme extends Model
{
    // Table name
    const TABLE_NAME = 'theme';
    protected $table = self::TABLE_NAME;

    /**
     * Formats the theme for the overview
     *
     * @return array
     */
    function formatForOverview() {
        return [
            'id'    => $this->id,
            'name'  => $this->name
        ];
    }
}
