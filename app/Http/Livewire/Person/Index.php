<?php

namespace App\Http\Livewire\Person;

use App\Models\Person;
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
     * The computed people property.
     *
     * @return LengthAwarePaginator
     */
    public function getPeopleProperty(): LengthAwarePaginator
    {
        $people = Person::query();

        // Search
        if (!empty($this->filter['search'])) {
            $searchTerms = explode(' ', str_replace([', ', ','], ' ', $this->filter['search']));

            $people = $people->where(function ($query) use ($searchTerms) {
                // Asian style
                $query->orWhere('first_name', 'like','%' . ($searchTerms[1] ?? $this->filter['search']) . '%');
                $query->orWhere('last_name', 'like','%' . $searchTerms[0] . '%');
                $query->orWhere('given_name', 'like','%' . ($searchTerms[1] ?? $this->filter['search']) . '%');
                $query->orWhere('family_name', 'like','%' . $searchTerms[0] . '%');

                // Wester style
                $query->orWhere('first_name', 'like','%' . $searchTerms[0] . '%');
                $query->orWhere('last_name', 'like','%' . ($searchTerms[1] ?? $this->filter['search']) . '%');
                $query->orWhere('given_name', 'like','%' . $searchTerms[0] . '%');
                $query->orWhere('family_name', 'like','%' . ($searchTerms[1] ?? $this->filter['search']) . '%');

            });
        }

        // Order
        if (!empty($this->filter['order_type'])) {
            $people = $people->orderBy('first_name', $this->filter['order_type']);
        }

        // Paginate
        return $people->paginate($this->filter['per_page'] ?? 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.index');
    }
}
