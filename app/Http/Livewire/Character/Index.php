<?php

namespace App\Http\Livewire\Character;

use App\Enums\AstrologicalSign;
use App\Enums\CharacterStatus;
use App\Models\Character;
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
    public static string $searchModel = Character::class;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Redirect the user to a random character.
     *
     * @return void
     */
    public function randomCharacter(): void
    {
        $character = Character::inRandomOrder()->first();
        $this->redirectRoute('characters.details', $character);
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.index');
    }
}
