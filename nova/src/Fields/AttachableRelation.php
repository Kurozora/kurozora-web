<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

trait AttachableRelation
{
    /**
     * Determines if the display values should be automatically sorted.
     *
     * @var (callable(\Laravel\Nova\Http\Requests\NovaRequest):bool)|bool
     */
    public $reordersOnAttachableCallback = true;

    /**
     * Determine if the display values should be automatically sorted when rendering attachable relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function shouldReorderAttachableValues(NovaRequest $request)
    {
        if (is_callable($this->reordersOnAttachableCallback)) {
            return call_user_func($this->reordersOnAttachableCallback, $request);
        }

        return $this->reordersOnAttachableCallback;
    }

    /**
     * Determine reordering on attachables.
     *
     * @return $this
     */
    public function dontReorderAttachables()
    {
        $this->reordersOnAttachableCallback = false;

        return $this;
    }

    /**
     * Determine reordering on attachables.
     *
     * @param  (callable(\Laravel\Nova\Http\Requests\NovaRequest):bool)|bool  $value
     * @return $this
     */
    public function reorderAttachables($value = true)
    {
        $this->reordersOnAttachableCallback = $value;

        return $this;
    }
}
