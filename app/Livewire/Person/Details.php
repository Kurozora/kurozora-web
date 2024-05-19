<?php

namespace App\Livewire\Person;

use App\Events\PersonViewed;
use App\Models\MediaRating;
use App\Models\Person;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

    /**
     * The object containing the character data.
     *
     * @var Person $person
     */
    public Person $person;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    public array $popupData = [
        'title' => '',
        'message' => '',
        'type' => 'default'
    ];

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
        // Call the PersonViewed event
        PersonViewed::dispatch($person);

        $this->person = $person->load(['media'])
            ->when(auth()->user(), function ($query, $user) use ($person) {
                return $person->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            }, function() use ($person) {
                return $person;
            });

        if (!auth()->check()) {
            $this->person->setRelation('mediaRatings', collect());
        }

        $this->userRating = $person->mediaRatings;
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.details');
    }
}
