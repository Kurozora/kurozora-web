<?php

namespace App\Livewire\Person;

use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the characters property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getCharactersProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->person->characters()
            ->with(['media', 'translations'])
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.characters');
    }
}
