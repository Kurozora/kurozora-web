<?php

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class MissingSongAttributes extends BooleanFilter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Missing Attributes';

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
        if ($value['am_id']) {
            $query->where('am_id',  '=', null);
        }
        if ($value['mal_id']) {
            $query->where('mal_id',  '=', null);
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
            'Apple Music ID' => 'am_id',
            'MAL ID' => 'mal_id',
        ];
    }
}
