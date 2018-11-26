<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumSection extends Model
{
    // Table name
    const TABLE_NAME = 'forum_section';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['name', 'icon', 'locked'];

    /**
     * Formats the section for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'locked'    => (bool) $this->locked
        ];
    }
}
