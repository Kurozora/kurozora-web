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

    /**
     * Get the amount of threads in this section
     *
     * @return integer
     */
    public function getThreadCount() {
        return ForumThread::where('section_id', $this->id)->count();
    }

    /**
     * Formats the section for a details response
     *
     * @return array
     */
    public function formatForDetails() {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'total_threads' => $this->getThreadCount(),
            'locked'        => (bool) $this->locked
        ];
    }
}
