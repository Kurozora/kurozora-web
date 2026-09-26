<?php

namespace App\Livewire\Person;

use App\Livewire\BaseMediaIndex;
use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Anime extends BaseMediaIndex
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
    protected string $view = 'livewire.person.anime';

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
     * The query returning the anime the person has worked on.
     *
     * @return Builder|Relation
     */
    protected function query(): Builder|Relation
    {
        return $this->person->anime();
    }
}
