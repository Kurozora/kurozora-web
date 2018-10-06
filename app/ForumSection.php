<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumSection extends Model
{
    // Table name
    protected $table = 'forum_section';

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
