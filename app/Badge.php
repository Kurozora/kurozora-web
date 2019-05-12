<?php

namespace App;

/**
 * @property mixed id
 * @property mixed text
 * @property mixed backgroundColor
 * @property mixed textColor
 * @property mixed description
 */
class Badge extends KModel
{
    // Table name
    const TABLE_NAME = 'badge';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the associated users with this badge
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function users() {
        return $this->belongsToMany(User::class, UserBadge::TABLE_NAME, 'badge_id', 'user_id');
    }

    /**
     * Formats the badge for a JSON response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'                => $this->id,
            'text'              => $this->text,
            'textColor'         => $this->textColor,
            'backgroundColor'   => $this->backgroundColor,
            'description'       => $this->description
        ];
    }
}
