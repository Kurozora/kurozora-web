<?php

namespace App\Http\Livewire\Studio;

use App\Enums\StudioType;
use App\Models\Studio;
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
    public function mount(): void {
        $this->setFilterableAttributes();
        $this->setOrderableAttributes();
    }

    /**
     * Redirect the user to a random studio.
     *
     * @return void
     */
    public function randomStudio(): void
    {
        $this->redirectRoute('studios.details', Studio::inRandomOrder()->first());
    }

    /**
     * The computed studios property.
     *
     * @return LengthAwarePaginator
     */
    public function getStudiosProperty(): LengthAwarePaginator
    {
        // Search
        $studios = Studio::search($this->search);

        // Order
        foreach ($this->order as $attribute => $order) {
            $selected = $order['selected'];
            if (!empty($selected)) {
                $studios = $studios->orderBy($attribute, $selected);
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
                            ->toISOString();
                        $studios = $studios->where($attribute, $date);
                        break;
                    case 'double':
                        $number = number_format($selected, 2, '.', '');
                        $studios = $studios->where($attribute, $number);
                        break;
                    default:
                        $studios = $studios->where($attribute, $selected);
                }
            }
        }

        // Paginate
        return $studios->paginate($this->perPage);
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
        return view('livewire.studio.index');
    }
}
