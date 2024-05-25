<?php

namespace App\Traits\Model;

trait HasSlug
{
    use \Spatie\Sluggable\HasSlug;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (request()->wantsJson() || (request()->route()?->middleware()[0] == 'api')) {
            return parent::getRouteKeyName();
        }
        return 'slug';
    }
}
