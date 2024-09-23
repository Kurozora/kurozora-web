<?php

namespace App\Traits\Livewire;

use App\Enums\VisualEffectViewStyle;
use App\Models\AppTheme;

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
