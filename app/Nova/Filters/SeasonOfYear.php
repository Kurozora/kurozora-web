<?php

namespace App\Nova\Filters;

use App\Nova\Game;
use App\Nova\Manga;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class SeasonOfYear extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param NovaRequest $request
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        return match ($request->resource()) {
            Manga::class, Game::class => $query->where('publication_season', '=', $value),
            default => $query->where('air_season', '=', $value)
        };
    }

    /**
     * Get the filter's available options.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function options(NovaRequest $request): array
    {
        return \App\Enums\SeasonOfYear::asArray();
    }
}
