<?php

namespace App\Http\Livewire\Components;

use App\Models\Manga;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class MangaMoreByStudioSection extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga
     */
    public Manga $manga;

    /**
     * The object containing the studio data.
     *
     * @var Studio
     */
    public Studio $studio;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Manga $manga, Studio $studio): void
    {
        $translations = $manga->translations;
        $this->manga = $manga->withoutRelations()
            ->setRelation('translations', $translations);
        $this->studio = $studio;
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
     * Loads the more by studio section.
     *
     * @return Collection
     */
    public function getMoreByStudioProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->studio->manga()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->where('model_id', '!=', $this->manga->id)
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.manga-more-by-studio-section');
    }
}
