<?php

namespace App\Livewire\Person;

use App\Livewire\BaseReviews;
use App\Models\Person;
use Illuminate\Database\Eloquent\Model;

class Reviews extends BaseReviews
{
    /**
     * The object containing the person data.
     *
     * @var Person $person
     */
    public Person $person;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.person.reviews';

    /**
     * Prepare the component.
     *
     * @param Person $person
     *
     * @return void
     */
    public function mount(Person $person): void
    {
        $this->person = $person->load(['media']);
    }

    /**
     * The person the reviews belong to.
     *
     * @return Model
     */
    protected function reviewable(): Model
    {
        return $this->person;
    }
}
