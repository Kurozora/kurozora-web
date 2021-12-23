<?php

namespace App\Http\Livewire\Person;

use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Characters extends Component
{
    use WithPagination;

    /**
     * The object containing the person data.
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
    public function mount(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.characters', [
            'personCharacters' => $this->person->characters()->paginate(25)
        ]);
    }
}
