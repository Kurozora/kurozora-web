<?php

namespace App\Livewire\Components;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Isolate]
#[Lazy]
class AnimeSeasonsSection extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $translation = $anime->translation;
        $this->anime = $anime->withoutRelations()
            ->setRelation('translation', $translation);
    }

    /**
     * The skeleton shown until the section resumes loading.
     *
     * @return View
     */
    public function placeholder(): View
    {
        return view('components.skeletons.section', ['lockup' => 'season']);
    }

    /**
     * Get the anime seasons.
     *
     * @return Collection
     */
    public function getSeasonsProperty(): Collection
    {
        return $this->anime->seasons()
            ->when($this->anime->tv_rating_id > request()->tvRating(), function ($query) {
                $query->withoutGlobalScopes();
            })
            ->with(['media', 'translation'])
            ->withCount([
                'episodes' => function ($query) {
                    $query->withoutGlobalScopes();
                }
            ])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->where('rating_average', '!=', 0);
                }
            ], 'rating_average')
            ->orderBy('number', 'desc')
            ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get()
            ->map(function(Season $season) {
                return $season->setRelation('anime', $this->anime);
            });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.anime-seasons-section');
    }
}
