<?php

namespace App\Http\Livewire\Person;

use App\Events\PersonViewed;
use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the character data.
     *
     * @var Person $person
     */
    public Person $person;

    /**
     * Prepare the component.
     *
     * @param Person $person
     *
     * @return void
     */
    public function mount(Person $person): void
    {
        // Call the PersonViewed event
        PersonViewed::dispatch($person);

        $this->person = $person;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.details', [
            'personAnime' => $this->person->getAnime(Person::MAXIMUM_RELATIONSHIPS_LIMIT),
            'personCharacters' => $this->person->getCharacters(Person::MAXIMUM_RELATIONSHIPS_LIMIT),
        ]);
    }
}
