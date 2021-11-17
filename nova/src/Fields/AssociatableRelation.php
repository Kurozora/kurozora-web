<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

trait AssociatableRelation
{
    /**
     * Determines if the display values should be automatically sorted.
     *
     * @var callable|bool
     */
    public $reordersOnAssociatableCallback = true;

    /**
     * Determine if the display values should be automatically sorted when rendering associatable relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function shouldReorderAssociatableValues(NovaRequest $request)
    {
        if (is_callable($this->reordersOnAssociatableCallback)) {
            return call_user_func($this->reordersOnAssociatableCallback, $request);
        }

        return $this->reordersOnAssociatableCallback;
    }

    /**
     * Determine reordering on associatables.
     *
     * @param  callable|bool  $callback
     * @return $this
     */
    public function dontReorderAssociatables()
    {
        $this->reordersOnAssociatableCallback = false;

        return $this;
    }

    /**
     * Determine reordering on associatables.
     *
     * @param  callable|bool  $callback
     * @return $this
     */
    public function reorderAssociatables($value = true)
    {
        $this->reordersOnAssociatableCallback = $value;

        return $this;
    }
}
