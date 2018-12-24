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

    // Amount of threads to display per page
    const THREADS_PER_PAGE = 10;

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
     * Get the amount of pages the section has
     *
     * @return integer
     */
    public function getPageCount() {
        return ceil($this->getThreadCount() / self::THREADS_PER_PAGE);
    }

    /**
     * Formats the section for a details response
     *
     * @return array
     */
    public function formatForDetailsResponse() {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'locked'        => (bool) $this->locked,
            'thread_pages'  => $this->getPageCount()
        ];
    }
}
