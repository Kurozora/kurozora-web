<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var ?Anime $anime
     */
    public ?Anime $anime;

    /**
     * The page's data.
     *
     * @var array
     */
    public array $page = [
        'title' => '',
        'image' => '',
        'type' => 'video.tv_show',
    ];

    /**
     * @param Anime $anime
     */
    public function mount(Anime $anime)
    {
        $this->anime = $anime;

        $this->page['title'] = $anime->title;
        $this->page['image'] = $anime->poster()->url ?? asset('img/static/placeholders/anime_poster.jpg');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.anime.details')
            ->layout('layouts.base');
    }
}
