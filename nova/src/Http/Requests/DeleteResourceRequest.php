<?php

namespace Laravel\Nova\Http\Requests;

use Closure;
use Illuminate\Support\Collection;

/**
 * @property-read string|array<int, mixed> $resources
 */
class DeleteResourceRequest extends DeletionRequest
{
    /**
     * Get the selected models for the action in chunks.
     *
     * @param  int  $count
     * @param  \Closure(\Illuminate\Support\Collection):void  $callback
     * @return mixed
     */
    public function chunks($count, Closure $callback)
    {
        return $this->chunkWithAuthorization($count, $callback, function ($models) {
            return $this->deletableModels($models);
        });
    }

    /**
     * Get the models that may be deleted.
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return \Illuminate\Support\Collection
     */
    protected function deletableModels(Collection $models)
    {
        return $models->mapInto($this->resource())
                        ->filter
                        ->authorizedToDelete($this)
                        ->map->model();
    }

    /**
     * Determine if the request is for a single resource only.
     *
     * @return bool
     */
    public function isForSingleResource()
    {
        return $this->resources !== 'all' && count($this->resources) == 1;
    }
}
