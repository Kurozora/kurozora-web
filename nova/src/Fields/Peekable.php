<?php

namespace Laravel\Nova\Fields;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Nova\Http\Requests\NovaRequest;

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

        if (is_null($relatedResource = $this->relatedResource())) {
            return false;
        }

        return $relatedResource->peekableFieldsCount($request) > 0;
    }

    /**
     * Return the appropriate related Resource for the field.
     *
     * @return \Laravel\Nova\Resource|null
     */
    protected function relatedResource()
    {
        if ($this instanceof MorphTo) {
            return $this->morphToResource;
        }

        return $this->belongsToResource;
    }
}
