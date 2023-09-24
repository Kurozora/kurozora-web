<?php

namespace App\Traits\Model;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasComments
{
    /**
     * Bootstrap the model with Comments.
     *
     * @return void
     */
    public static function bootHasComments(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->comments()->forceDelete();
                    return;
                }
            }

            $model->comments()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->comments()->restore();
            });
        }
    }

    /**
     * Get the model's comments.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Attach a comment to this model.
     *
     * @param string $comment
     * @return Model
     */
    public function comment(string $comment): Model
    {
        return $this->commentAsUser(auth()->user(), $comment);
    }

    /**
     * Attach a comment to the model as a specific user.
     *
     * @param User $user
     * @param string $comment
     * @return Model
     */
    public function commentAsUser(User $user, string $comment): Model
    {
        return $this->comments()->create([
            'user_id' => $user->id,
            'content' => $comment,
        ]);
    }
}
