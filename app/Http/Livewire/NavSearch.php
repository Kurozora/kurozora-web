<?php

namespace App\Http\Livewire;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Person;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavSearch extends Component
{
    /**
     * The search query string.
     *
     * @var string $searchQuery
     */
    public string $searchQuery = '';

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $this->redirect(route('anime.details', Anime::inRandomOrder()->first()));
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $searchResults = [];
        $searchResultsTotal = 0;

        if (!empty($this->searchQuery)) {
            $anime = Anime::search($this->searchQuery)
                ->paginate(5);
            $characters = Character::search($this->searchQuery)
                ->paginate(5);
            $people = Person::search($this->searchQuery)
                ->paginate(5);
            $studios = Studio::search($this->searchQuery)
                ->paginate(5);
            $users = User::search($this->searchQuery)
                ->paginate(5);

            $searchResults['anime'] = $anime;
            $searchResults['characters'] = $characters;
            $searchResults['people'] = $people;
            $searchResults['studios'] = $studios;
            $searchResults['users'] = $users;
            $searchResultsTotal = $anime->total() + $characters->total() + $people->total() + $studios->total() + $users->total();
        }

        return view('livewire.nav-search', [
            'searchResults' => $searchResults,
            'searchResultsTotal' => $searchResultsTotal,
            'quickLinks'    => [
                [
                    'title'  => __('Random Anime'),
                    'action' => 'randomAnime',
                ],
                [
                    'title' => __('About Kurozora+'),
                    'link'  => route('kb.iap'),
                ],
                [
                    'title' => __('About Personalisation'),
                    'link'  => route('kb.personalisation'),
                ],
                [
                    'title' => __('Welcome to Kurozora'),
                    'link'  => route('welcome'),
                ]
            ],
        ]);
    }
}
