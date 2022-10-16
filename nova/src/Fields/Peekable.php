<?php

namespace Laravel\Nova\Fields;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

trait Peekable
{
    /**
     * Indicates if the related resource can be peeked at.
     *
     * @var bool|\Closure|null
     */
    public $peekable = true;

    /**
     * Specify if the related resource can be peeked at.
     *
     * @param  (callable(\Laravel\Nova\Http\Requests\NovaRequest):bool)|bool  $callback
     * @return $this
     */
    public function peekable($callback = true)
    {
        $this->peekable = $callback;

        return $this;
    }

    /**
     * Prevent the user from peeking at the related resource.
     *
     * @return $this
     */
    public function noPeeking()
    {
        $this->peekable = false;

        return $this;
    }

    /**
     * Resolve whether the relation is able to be peeked at.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function isPeekable(NovaRequest $request)
    {
        if (is_callable($this->peekable)) {
            $this->peekable = call_user_func($this->peekable, $request);
        }

        return $this->peekable;
    }

    /**
     * Determine if the relation has fields that can be peeked at.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function hasFieldsToPeekAt(NovaRequest $request)
    {
        if (! $request->isPresentationRequest()) {
            return false;
        }

        if ($this instanceof MorphTo) {
            $resourceClass = class_exists($this->morphToType)
                ? $this->resolveResourceClass(new $this->morphToType)
                : Nova::resourceForKey($this->morphToType);
            $this->resourceClass = null;

            if (! is_string($resourceClass)) {
                return false;
            }
        } else {
            $resourceClass = $this->resourceClass;
        }

        $relationship = $resourceClass::newModel()->query()->find($this->relatedId());

        return (new $resourceClass($relationship))->peekableFieldsCount($request) > 0;
    }

    /**
     * Return the appropriate related ID for the field.
     *
     * @return string
     */
    protected function relatedId()
    {
        if ($this instanceof MorphTo) {
            return $this->morphToId;
        }

        return $this->belongsToId;
    }
}
