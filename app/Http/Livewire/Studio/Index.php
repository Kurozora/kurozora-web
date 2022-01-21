<?php

namespace App\Http\Livewire\Studio;

use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The component's filters.
     *
     * @var array $filter
     */
    public array $filter = [
        'search' => '',
        'order_type' => '',
        'per_page' => 25,
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount() {}

    /**
     * The computed studios property.
     *
     * @return LengthAwarePaginator
     */
    public function getStudiosProperty(): LengthAwarePaginator
    {
        $studios = Studio::query();

        // Search
        if (!empty($this->filter['search'])) {
            $studios = $studios->where('name', 'like', '%' . $this->filter['search'] . '%');
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $studios = $studios->orderBy('name', $this->filter['order_type']);
        }

        // Paginate
        return $studios->paginate($this->filter['per_page'] ?? 25);
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
