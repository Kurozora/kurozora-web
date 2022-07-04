<?php

namespace App\Http\Livewire\Person;

use App\Enums\AstrologicalSign;
use App\Models\Person;
use Carbon\Carbon;
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
     * The search string.
     *
     * @var string $search
     */
    public string $search = '';

    /**
     * The number of results per page.
     *
     * @var int $perPage
     */
    public int $perPage = 25;

    /**
     * The component's filter attributes.
     *
     * @var array $filter
     */
    public array $filter = [];

    /**
     * The component's order attributes.
     *
     * @var array $order
     */
    public array $order = [];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
    }

    /**
     * Redirect the user to a random person.
     *
     * @return void
     */
    public function randomPerson()
    {
        $this->redirectRoute('people.details', Person::inRandomOrder()->first());
    }

    /**
     * The computed people property.
     *
     * @return LengthAwarePaginator
     */
    public function getPeopleProperty(): LengthAwarePaginator
    {
        // Search
        $people = Person::search($this->search);

        // Order
        foreach ($this->order as $attribute => $order) {
            $selected = $order['selected'];
            if (!empty($selected)) {
                $people = $people->orderBy($attribute, $selected);
            }
        }

        // Filter
        foreach ($this->filter as $attribute => $filter) {
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                switch ($type) {
                    case 'date':
                        $date = Carbon::createFromFormat('Y-m-d', $selected)
                            ->setTime(0, 0)
                            ->timestamp;
                        $people = $people->where($attribute, $date);
                        break;
                    case 'time':
                        $time = $selected . ':00';
                        $people = $people->where($attribute, $time);
                        break;
                    case 'double':
                        $number = number_format($selected, 2, '.', '');
                        $people = $people->where($attribute, $number);
                        break;
                    default:
                        $people = $people->where($attribute, $selected);
                }
            }
        }

        // Paginate
        return $people->paginate($this->perPage);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return void
     */
    public function setOrderableAttributes(): void
    {
        $this->order = [
            'full_name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'birthdate' => [
                'title' => __('Birthday'),
                'options' => [
                    'Default' => null,
                    'Youngest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'deceased_date' => [
                'title' => __('Deceased Date'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'astrological_sign' => [
                'title' => __('Astrological Sign'),
                'options' => [
                    'Default' => null,
                    'Aries-Pisces' => 'asc',
                    'Pisces-Aries' => 'desc',
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
            'birthdate' => [
                'title' => __('Birthday'),
                'type' => 'date',
                'selected' => null,
            ],
            'deceased_date' => [
                'title' => __('Deceased Date'),
                'type' => 'date',
                'selected' => null,
            ],
            'astrological_sign' => [
                'title' => __('Astrological Sign'),
                'type' => 'select',
                'options' => AstrologicalSign::asSelectArray(),
                'selected' => null,
            ],
        ];
    }

    /**
     * Reset order to default values.
     *
     * @return void
     */
    public function resetOrder(): void
    {
        $this->order = array_map(function ($order) {
            $order['selected'] = null;
            return $order;
        }, $this->order);
    }

    /**
     * Reset filter to default values.
     *
     * @return void
     */
    public function resetFilter(): void
    {
        $this->filter = array_map(function ($filter) {
            $filter['selected'] = null;
            return $filter;
        }, $this->filter);
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
