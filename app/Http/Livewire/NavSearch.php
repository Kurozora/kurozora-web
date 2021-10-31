<?php

namespace App\Http\Livewire;

use App\Models\Anime;
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $searchResults = [];

        if (!empty($this->searchQuery)) {
            $searchResults = [
                'anime' => Anime::kSearch($this->searchQuery)
//                    dd(Anime::kSearch($this->searchQuery)
//                    ->getQuery()->dump()),
                    ->paginate(Anime::MAX_WEB_SEARCH_RESULTS),
//                    ->appends('query', $this->searchQuery),
//                'users' => User::kSearch($this->searchQuery)->paginate(User::MAX_WEB_SEARCH_RESULTS)
//                    ->appends('query', $this->searchQuery)
            ];
        }

        return view('livewire.nav-search', [
            'searchResults' => $searchResults,
            'quickLinks' => [
                [
                    'title' => __('About Kurozora+'),
                    'link'  => '#',
                ],
                [
                    'title' => __('About Personalisation'),
                    'link'  => '#',
                ],
                [
                    'title' => __('Welcome to Kurozora'),
                    'link'  => '#',
                ]
            ]
        ]);
    }
}
