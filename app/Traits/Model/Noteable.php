<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserNote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Noteable
{
    /**
     * Bootstrap the model with Notes.
     *
     * @return void
     */
    public static function bootNoteable(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->notes()->forceDelete();
                    return;
                }
            }

            $model->notes()->delete();
        });
    }

    /**
     * Get the model's notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(UserNote::class, 'noteable');
    }

    /**
     * Returns the given user's note on the model.
     *
     * @param User $user
     *
     * @return null|UserNote
     */
    public function noteBy(User $user): ?UserNote
    {
        if ($this->relationLoaded('notes')) {
            return $this->notes->firstWhere('user_id', '=', $user->id);
        }

        return $this->notes()
            ->where('user_id', '=', $user->id)
            ->first();
    }
}
