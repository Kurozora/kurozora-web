<?php

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class IsNsfw extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value): Builder
    {
        foreach($value as $attribute => $enabled) {
            if (!$enabled) {
                continue;
            }

            $query->where($attribute, '=', $enabled);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     * @return array
     */
    public function options(Request $request): array
    {
        return [
            'Is NSFW' => 'is_nsfw',
        ];
    }
}
