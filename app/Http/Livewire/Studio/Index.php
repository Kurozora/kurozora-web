<?php

namespace App\Http\Livewire\Studio;

use App\Enums\StudioType;
use App\Models\Studio;
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
    public static string $searchModel = Studio::class;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Redirect the user to a random studio.
     *
     * @return void
     */
    public function randomStudio(): void
    {
        $studio = Studio::inRandomOrder()->first();
        $this->redirectRoute('studios.details', $studio);
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
            'address' => [
                'title' => __('Address'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'founded' => [
                'title' => __('Founded'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
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
            'type' => [
                'title' => __('Type'),
                'type' => 'select',
                'options' => StudioType::asSelectArray(),
                'selected' => null,
            ],
            'address' => [
                'title' => __('Address'),
                'type' => 'string',
                'selected' => null,
            ],
            'founded' => [
                'title' => __('Founded'),
                'type' => 'date',
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
        return view('livewire.studio.index');
    }
}
