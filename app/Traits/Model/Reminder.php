<?php

namespace App\Traits\Model;

use App\Models\UserReminder;
use Illuminate\Database\Eloquent\Collection;
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
     *
     * @return MorphToMany
     */
    protected function remindedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'remindable', UserReminder::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the user has reminded the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasReminded(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return false;
        }

        $modelType = $models->first()->getMorphClass();
        $modelIDs = $models->pluck('id')->all();

        return ($this->relationLoaded('reminders') ? $this->reminders : $this->reminders())
                ->where('remindable_type', '=', $modelType)
                ->whereIn('remindable_id', $modelIDs)
                ->count() === count($modelIDs);
    }

    /**
     * Whether the user has not reminded the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasNotReminded(Model|array|Collection $models): bool
    {
        return !$this->hasReminded($models);
    }

    /**
     * Reminder the given models.
     *
     * @param Model|Model[] $models
     *
     * @return void
     */
    public function remind(Model|array|Collection $models): void
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return;
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        $this->remindedModels($modelType)
            ->attach($modelKeys);
    }

    /**
     * Un-remind the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function unremind(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return true;
        }

        if ($this->relationLoaded('reminders')) {
            $this->unsetRelation('reminders');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        return (bool) $this->remindedModels($modelType)
            ->detach($modelKeys);
    }

    /**
     * Clears the reminders of the given type.
     *
     * @param string|null $type
     *
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
     * Toggle remind status of the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function toggleReminder(Model|array|Collection $models): bool
    {
        if ($this->hasReminded($models)) {
            $this->unremind($models);
            return false;
        } else {
            $this->remind($models);
            return true;
        }
    }

    /**
     * Eloquent builder scope that limits the query to the models of the specified type.
     *
     * @param string $type
     *
     * @return BelongsToMany
     */
    public function whereReminded(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserReminder::class, 'user_id', 'remindable_id')
            ->where('remindable_type', '=', $type)
            ->withTimestamps();
    }
}
