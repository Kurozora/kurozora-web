<?php

namespace App\Traits\Model;

use App\Enums\UserLibraryStatus;
use App\Models\UserLibrary;
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
     * Whether the user has tracked the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function hasTracked(Model $model): bool
    {
        return ($this->relationLoaded('library') ? $this->library : $this->library())
            ->where('trackable_id', '=', $model->getKey())
            ->where('trackable_type', '=', $model->getMorphClass())
            ->exists();
    }

    /**
     * Whether the user has not tracked the given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function hasNotTracked(Model $model): bool
    {
        return !$this->hasTracked($model);
    }

    /**
     * Track the given model.
     *
     * @param Model             $model
     * @param UserLibraryStatus $status
     *
     * @return UserLibrary
     */
    public function track(Model $model, UserLibraryStatus $status): UserLibrary
    {
        $attributes = [
            'trackable_type' => $model->getMorphClass(),
            'trackable_id' => $model->getKey(),
        ];

        return $this->library()
            ->where($attributes)
            ->firstOr(function () use ($status, $attributes) {
                $libraryLoaded = $this->relationLoaded('library');

                if ($libraryLoaded) {
                    $this->unsetRelation('library');
                }

                $attributes['status'] = $status->value;
                return $this->library()
                    ->create($attributes);
            });
    }

    /**
     * Un-track the given model
     *
     * @param Model $model
     *
     * @return bool
     */
    public function untrack(Model $model): bool
    {
        $hasNotTracked = $this->hasNotTracked($model);

        if ($hasNotTracked) {
            return true;
        }

        $libraryLoaded = $this->relationLoaded('library');
        if ($libraryLoaded) {
            $this->unsetRelation('library');
        }

        return (bool) $this->trackedModels(get_class($model))
            ->detach($model->getKey());
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
     * Toggle tracking status of the given model.
     *
     * @param Model $model
     *
     * @return UserLibrary|bool
     */
    public function toggleTracking(Model $model): bool|UserLibrary
    {
        return $this->hasTracked($model)
            ? $this->untrack($model)
            : $this->track($model, UserLibraryStatus::InProgress());
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
        return $this->belongsToMany($type, UserLibrary::class, 'user_id', UserLibrary::TABLE_NAME . '.trackable_id')
            ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $type)
            ->withPivot(UserLibrary::TABLE_NAME . '.status') // Needed for get library API
            ->withTimestamps();
    }
}
