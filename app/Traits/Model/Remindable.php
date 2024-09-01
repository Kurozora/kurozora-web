<?php

namespace App\Traits\Model;

use App\Models\User;
use App\Models\UserReminder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Remindable
{
    /**
     * Bootstrap the model with Reminders.
     *
     * @return void
     */
    public static function bootRemindable(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->reminders()->forceDelete();
                    return;
                }
            }

            $model->reminders()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->reminders()->restore();
            });
        }
    }

    /**
     * Get the model's reminded entries.
     *
     * @return MorphMany
     */
    function reminders(): MorphMany
    {
        return $this->morphMany(UserReminder::class, 'remindable');
    }

    /**
     * The users who reminded the model.
     *
     * @return BelongsToMany
     */
    public function reminderers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserReminder::class, 'remindable_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Whether the model is reminded by the given user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isRemindedBy(User $user): bool
    {
        $remindersLoaded = $this->relationLoaded('reminders');

        if ($remindersLoaded) {
            return $this->reminders->contains($user);
        }

        return (
            $this->relationLoaded('reminderers')
                ? $this->reminderers
                : $this->reminderers()
        )
            ->where('user_id', '=', $user->id)
            ->exists();
    }

    /**
     * Whether the model is not reminded by the given user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isNotRemindedBy(User $user): bool
    {
        return !$this->isRemindedBy($user);
    }

    /**
     * The number of users who reminded the model.
     *
     * @return int
     */
    public function remindersCount(): int
    {
        if ($this->reminders_count !== null) {
            return (int) $this->reminders_count;
        }

        $this->loadCount('reminders');

        return (int) $this->reminders_count;
    }

    /**
     * The formatted number of users who reminded the model.
     *
     * @param int  $precision
     * @param bool $abbreviated
     *
     * @return string
     */
    public function remindersCountForHumans(int $precision = 1, bool $abbreviated = false): string
    {
        return number_shorten($this->remindersCount(), $precision, $abbreviated);
    }

    /**
     * Eloquent builder scope that limits the query to the models reminded by the user.
     *
     * @param Builder $query
     * @param Model   $user
     *
     * @return Builder
     */
    public function scopeWhereRemindedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas('reminders', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }

    /**
     * Eloquent builder scope that limits the query to the models not reminded by the user.
     *
     * @param Builder $query
     * @param Model   $user
     *
     * @return Builder
     */
    public function scopeWhereNotRemindedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave('reminders', function (Builder $query) use ($user): Builder {
            return $query->whereKey($user->getKey());
        });
    }
}
