<?php

namespace App\Traits\Model;

use App\Models\UserReminder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Reminder
{
    /**
     * The user's reminded models.
     *
     * @return HasMany
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(UserReminder::class);
    }

    /**
     * The models reminded by the user.
     *
     * @param string $type
     * @return MorphToMany
     */
    protected function remindedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'remindable', UserReminder::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the user has reminded the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasReminded(Model $model): bool
    {
        return ($this->relationLoaded('reminders') ? $this->reminders : $this->reminders())
            ->where('remindable_id', $model->getKey())
            ->where('remindable_type', $model->getMorphClass())
            ->exists();
    }

    /**
     * Whether the user has not reminded the given model.
     *
     * @param Model $model
     * @return bool
     */
    public function hasNotReminded(Model $model): bool
    {
        return !$this->hasReminded($model);
    }

    /**
     * Reminder the given model.
     *
     * @param Model $model
     * @return UserReminder
     */
    public function remind(Model $model): UserReminder
    {
        $attributes = [
            'remindable_id' => $model->getKey(),
            'remindable_type' => $model->getMorphClass(),
        ];

        return $this->reminders()
            ->where($attributes)
            ->firstOr(function () use ($attributes) {
                $remindsLoaded = $this->relationLoaded('reminders');

                if ($remindsLoaded) {
                    $this->unsetRelation('reminders');
                }

                return $this->reminders()
                    ->create($attributes);
            });
    }

    /**
     * Un-remind the given model
     *
     * @param Model $model
     * @return bool
     */
    public function unremind(Model $model): bool
    {
        $hasNotReminded = $this->hasNotReminded($model);

        if ($hasNotReminded) {
            return true;
        }

        $remindsLoaded = $this->relationLoaded('reminders');
        if ($remindsLoaded) {
            $this->unsetRelation('reminders');
        }

        return (bool) $this->remindedModels($model::class)
            ->detach($model->getKey());
    }

    /**
     * Clears the reminders of the given type.
     *
     * @param string|null $type
     * @return bool
     */
    public function clearReminders(?string $type = null): bool
    {
        return $this->reminders()
            ->when($type != null, function ($query) use ($type) {
                $query->where('remindable_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Toggle remind status of the given model.
     *
     * @param Model $model
     * @return UserReminder|bool
     */
    public function toggleReminder(Model $model): bool|UserReminder
    {
        return $this->hasReminded($model)
            ? $this->unremind($model)
            : $this->remind($model);
    }

    /**
     * Eloquent builder scope that limits the query to the models of the specified type.
     *
     * @param string $type
     * @return BelongsToMany
     */
    public function whereReminded(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserReminder::class, 'user_id', 'remindable_id')
            ->where('remindable_type', '=', $type)
            ->withTimestamps();
    }
}
