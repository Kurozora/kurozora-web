<?php

namespace App\Traits\Livewire;

use App\Enums\AstrologicalSign;
use App\Models\Person;

trait WithPersonSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Person::class;

    /**
     * Redirect the user to a random person.
     *
     * @return void
     */
    public function randomPerson(): void
    {
        $person = Person::inRandomOrder()->first();
        $this->redirectRoute('people.details', $person);
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
}
