<?php

namespace App\Http\Livewire\ThemeStore;

use App\Enums\VisualEffectViewStyle;
use App\Models\AppTheme;
use App\Traits\Livewire\WithSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = AppTheme::class;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = [
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
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = [
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

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme-store.index');
    }
}
