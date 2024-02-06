<?php

namespace App\Models;

use App\Traits\Model\HasComments;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends KModel
{
    use HasComments,
        HasUlids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'comments';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    /**
     * Get the commentable entity that the comment belongs to.
     *
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the comment belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The replies to the comment.
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    /**
     * Toggle the spoiler status of the comment.
     * Pass a value to set the spoiler value to your desired state.
     *
     * @param bool|null $spoiler
     *
     * @return Comment
     */
    public function toggleSpoiler(?bool $spoiler = null): Comment
    {
        return tap($this)->update([
            'is_spoiler' => $lock ?? !$this->is_spoiler,
        ]);
    }

    /**
     * Toggle the NSFW status of the comment.
     * Pass a value to set the NSFW value to your desired state.
     *
     * @param bool|null $nsfw
     *
     * @return Comment
     */
    public function toggleNSFW(?bool $nsfw = null): Comment
    {
        return tap($this)->update([
            'is_nsfw' => $lock ?? !$this->is_nsfw,
        ]);
    }

    /**
     * Toggle the NSFL status of the comment.
     * Pass a value to set the NSFL value to your desired state.
     *
     * @param bool|null $nsfl
     *
     * @return Comment
     */
    public function toggleNSFL(?bool $nsfl = null): Comment
    {
        return tap($this)->update([
            'is_nsfl' => $lock ?? !$this->is_nsfl,
        ]);
    }

    /**
     * Toggle the lock status of the comment.
     * Pass a value to set the lock value to your desired state.
     *
     * @param bool|null $lock
     *
     * @return Comment
     */
    public function toggleLock(?bool $lock = null): Comment
    {
        return tap($this)->update([
            'is_locked' => $lock ?? !$this->is_locked,
        ]);
    }
}
