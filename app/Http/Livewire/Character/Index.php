<?php

namespace App\Http\Livewire\Character;

use App\Models\Character;
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
     * Indicates whether the filter dropdown is open.
     *
     * @var bool $isFilterOpen
     */
    public bool $isFilterOpen = false;

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
     * The component's listeners.
     *
     * @var array $listeners
     */
    protected $listeners = [
        'load_characters' => 'characters'
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount() {}

    /**
     * The computed characters property.
     *
     * @return LengthAwarePaginator
     */
    public function getCharactersProperty(): LengthAwarePaginator
    {
        $characters = Character::query();

        // Search
        if (!empty($this->filter['search'])) {
            $characters = $characters->whereTranslationLike('name', '%' . $this->filter['search'] . '%');
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $characters = $characters->orderByTranslation('name', $this->filter['order_type']);
        }

        // Paginate
        return $characters->paginate($this->filter['per_page'] ?? 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.index');
    }
}
