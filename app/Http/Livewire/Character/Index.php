<?php

namespace App\Http\Livewire\Character;

use App\Enums\AstrologicalSign;
use App\Enums\CharacterStatus;
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
     * Redirect the user to a random character.
     *
     * @return void
     */
    public function randomCharacter(): void
    {
        $this->redirectRoute('characters.details', Character::inRandomOrder()->first());
    }

    /**
     * The computed characters property.
     *
     * @return LengthAwarePaginator
     */
    public function getCharactersProperty(): LengthAwarePaginator
    {
        // Search
        $characters = Character::search($this->search);

        // Order
        foreach ($this->order as $attribute => $order) {
            $selected = $order['selected'];
            if (!empty($selected)) {
                $characters = $characters->orderBy($attribute, $selected);
            }
        }

        // Filter
        foreach ($this->filter as $attribute => $filter) {
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                switch ($type) {
                    case 'double':
                        $number = number_format($selected, 2, '.', '');
                        $characters = $characters->where($attribute, $number);
                        break;
                    default:
                        $characters = $characters->where($attribute, $selected);
                }
            }
        }

        // Paginate
        return $characters->paginate($this->perPage);
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
            'age' => [
                'title' => __('Age'),
                'options' => [
                    'Default' => null,
                    'Youngest' => 'asc',
                    'Oldest' => 'desc',
                ],
                'selected' => null,
            ],
            'height' => [
                'title' => __('Height'),
                'options' => [
                    'Default' => null,
                    'Shortest' => 'asc',
                    'Tallest' => 'desc',
                ],
                'selected' => null,
            ],
            'weight' => [
                'title' => __('Weight'),
                'options' => [
                    'Default' => null,
                    'Lightest' => 'asc',
                    'Heaviest' => 'desc',
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
            'status' => [
                'title' => __('Status'),
                'type' => 'select',
                'options' => CharacterStatus::asSelectArray(),
                'selected' => null,
            ],
            'age' => [
                'title' => __('Age'),
                'type' => 'double',
                'selected' => null,
            ],
            'birth_day' => [
                'title' => __('Birth Day'),
                'type' => 'day',
                'selected' => null,
            ],
            'birth_month' => [
                'title' => __('Birth Month'),
                'type' => 'month',
                'selected' => null,
            ],
            'height' => [
                'title' => __('Height (cm)'),
                'type' => 'double',
                'selected' => null,
            ],
            'weight' => [
                'title' => __('Weight (grams)'),
                'type' => 'double',
                'selected' => null,
            ],
            'bust' => [
                'title' => __('Bust'),
                'type' => 'double',
                'selected' => null,
            ],
            'waist' => [
                'title' => __('Waist'),
                'type' => 'double',
                'selected' => null,
            ],
            'hip' => [
                'title' => __('Hip'),
                'type' => 'double',
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
        return view('livewire.character.index');
    }
}
