<?php

namespace App\Livewire\Components\Anime;

use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class MangaRelationsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $translations = $anime->translations;
        $this->anime = $anime->withoutRelations()
            ->setRelation('translations', $translations);
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Loads the manga relations section.
     *
     * @return Collection
     */
    public function getMangaRelationsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->anime->mangaRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation'
            ])
            ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime.manga-relations-section');
    }
}
