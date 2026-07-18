<?php

namespace App\Traits\Model;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Models\UserLibrary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;

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

        // `withTrashed()` avoids colliding with an existing tombstone on the unique key.
        foreach ($models as $model) {
            UserLibrary::withTrashed()->updateOrCreate([
                'user_id' => $this->id,
                'trackable_type' => $modelType,
                'trackable_id' => $model->getKey(),
            ], [
                'status' => $status->value,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Soft-deletes the user's library entries for the given models.
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
        $modelKeys = $models->map(fn($model) => $model->getKey())->all();

        // Bulk soft-delete bypasses model events; unsearchable explicitly before deleting.
        $this->library()
            ->where('trackable_type', '=', $modelType)
            ->whereIn('trackable_id', $modelKeys)
            ->unsearchable();

        return (bool) $this->trackedModels($modelType)
            ->detach($modelKeys);
        $untracked = (bool) $this->library()
            ->where('trackable_type', '=', $modelType)
            ->whereIn('trackable_id', $modelKeys)
            ->delete();

        // You can't have watched episodes of an anime that's no longer in the library.
        if ($modelType === Anime::class) {
            $episodeIDs = Episode::withoutGlobalScopes()
                ->whereIn('season_id', Season::withoutGlobalScopes()
                    ->whereIn('anime_id', $modelKeys)
                    ->select('id'))
                ->select('id');

        return $untracked;
    }

    /**
     * Soft-deletes every library entry of the given type.
     *
     * @param string|null $type
     *
     * @return bool
     * @throws ConnectionException
     */
    public function clearLibrary(?string $type = null): bool
    {
        // Bulk soft-delete bypasses model events, so Scout never removes the rows on
        // its own — unsearchable them first, while the non-trashed scope still sees them.
        $this->library()
            ->when($type != null, function ($query) use ($type) {
                $query->where('trackable_type', '=', $type);
            })
            ->forceDelete();
            ->unsearchable();

        $cleared = (bool) $this->library()
            ->when($type != null, function ($query) use ($type) {
                $query->where('trackable_type', '=', $type);
            })
            ->delete();

        return $cleared;
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
            ->wherePivotNull('deleted_at')
            ->withPivot('status') // Needed for GET library API
            ->withTimestamps();
    }
}
