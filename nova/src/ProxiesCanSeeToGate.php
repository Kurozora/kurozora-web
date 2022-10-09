<?php

namespace Laravel\Nova;

use Illuminate\Support\Arr;

trait ProxiesCanSeeToGate
{
    /**
     * Indicate that the entity can be seen when a given authorization ability is available.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return $this
     */
    public function canSeeWhen($ability, $arguments = [])
    {
        $arguments = Arr::wrap($arguments);

        if (isset($arguments[0]) && $arguments[0] instanceof Resource) {
            $arguments[0] = $arguments[0]->resource;
        }

        return $this->canSee(function ($request) use ($ability, $arguments) {
            return Nova::user($request)->can($ability, $arguments);
        });
    }
}
