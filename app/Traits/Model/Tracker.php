<?php

namespace App\Traits\Model;

use App\Enums\UserLibraryStatus;
use App\Models\UserLibrary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Tracker
{
    /**
     * The user's library.
     *
     * @return HasMany
     */
    public function library(): HasMany
    {
        return $this->hasMany(UserLibrary::class);
    }

    /**
     * The models tracked by the user.
     *
     * @param string $type
     *
     * @return MorphToMany
     */
    protected function trackedModels(string $type): MorphToMany
    {
        return $this->morphedByMany($type, 'trackable', UserLibrary::TABLE_NAME)
            ->withTimestamps();
    }

    /**
     * Whether the user has tracked the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasTracked(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return false;
        }

        $type = $models->first()->getMorphClass();
        $ids = $models->pluck('id')->all();

        return ($this->relationLoaded('library') ? $this->library : $this->library())
                ->where('trackable_type', '=', $type)
                ->whereIn('trackable_id', $ids)
                ->count() === count($ids);
    }

    /**
     * Whether the user has not tracked the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function hasNotTracked(Model|array|Collection $models): bool
    {
        return !$this->hasTracked($models);
    }

    /**
     * Track the given models.
     *
     * @param Model|Model[]     $models
     * @param UserLibraryStatus $status
     *
     * @return void
     */
    public function track(Model|array|Collection $models, UserLibraryStatus $status): void
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return;
        }

        if ($this->relationLoaded('library')) {
            $this->unsetRelation('library');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());
        $attributes = [
            'status' => $status->value
        ];

        $this->trackedModels($modelType)
            ->attach($modelKeys, $attributes);
    }

    /**
     * Un-track the given models.
     *
     * @param Model|Model[] $models
     *
     * @return bool
     */
    public function untrack(Model|array|Collection $models): bool
    {
        if ($models instanceof Model) {
            $models = collect([$models]);
        } else {
            $models = collect($models);
        }

        if ($models->isEmpty()) {
            return true;
        }

        if ($this->relationLoaded('library')) {
            $this->unsetRelation('library');
        }

        $modelType = $models->first()->getMorphClass();
        $modelKeys = $models->map(fn($model) => $model->getKey());

        return (bool) $this->trackedModels($modelType)
            ->detach($modelKeys);
    }

    /**
     * Clears the library of the given type.
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function clearLibrary(?string $type = null): bool
    {
        return $this->library()
            ->when($type != null, function ($query) use ($type) {
                $query->where('trackable_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Toggle tracking status of the given models.
     *
     * @param Model $model
     *
     * @return UserLibrary|bool
     */
    public function toggleTracking(Model $model): bool|UserLibrary
    {
        if ($this->hasTracked($model)) {
            $this->untrack($model);
            return false;
        } else {
            $this->track($model, UserLibraryStatus::InProgress());
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
    public function whereTracked(string $type): BelongsToMany
    {
        return $this->belongsToMany($type, UserLibrary::class, 'user_id', 'trackable_id')
            ->where('trackable_type', '=', $type)
            ->withPivot('status') // Needed for GET library API
            ->withTimestamps();
    }
}
