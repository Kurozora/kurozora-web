<?php

namespace App\Nova\Lenses;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class DesyncedEpisodeCount extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param LensRequest $request
     * @param Builder     $query
     *
     * @return mixed
     */
    public static function query(LensRequest $request, $query): mixed
    {
        return $query->withoutGlobalScopes()
            ->whereNotNull('episode_count')
            ->whereHas('episodes', function ($subQuery) {
                $subQuery->select('season_id')
                    ->groupBy('season_id')
                    ->havingRaw('COUNT(*) != animes.episode_count');
            })
            ->withCount('episodes');
    }

    /**
     * Get the fields available to the lens.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->sortable(),

            Text::make('Title', 'original_title'),

            Text::make('MLA ID', 'mal_id'),

            Text::make('TVDB ID', 'tvdb_id'),

            Number::make('Expected Count', 'episode_count'),

            Number::make('Actual Count', 'episodes_count')
                ->sortable(),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     */
    public function uriKey(): string
    {
        return 'desynced-episode-count';
    }
}
