<?php

namespace App\Traits\Livewire;

use App\Enums\VisualEffectViewStyle;
use App\Models\AppTheme;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithThemeStoreSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = AppTheme::class;

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'name';
    }

    /**
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->with(['media']);
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['media']);
        });
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return [
            'name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'download_count' => [
                'title' => __('Download Count'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
            'version' => [
                'title' => __('Version'),
                'options' => [
                    'Default' => null,
                    '0-9' => 'asc',
                    '9-0' => 'desc',
                ],
                'selected' => null,
            ],
        ];
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return [
            'download_count' => [
                'title' => __('Download Count'),
                'type' => 'number',
                'selected' => null,
            ],
            'version' => [
                'title' => __('Version'),
                'type' => 'string',
                'selected' => null,
            ],
            'ui_status_bar_style' => [
                'title' => __('Appearance'),
                'type' => 'select',
                'options' => VisualEffectViewStyle::asSelectArray(),
                'selected' => null,
            ],
        ];
    }
}
