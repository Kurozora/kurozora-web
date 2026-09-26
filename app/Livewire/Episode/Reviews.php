<?php

namespace App\Livewire\Episode;

use App\Livewire\BaseReviews;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Model;

class Reviews extends BaseReviews
{
    /**
     * The object containing the episode data.
     *
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.episode.reviews';

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     *
     * @return void
     */
    public function mount(Episode $episode): void
    {
        $this->episode = $episode->load(['media']);
    }

    /**
     * The episode the reviews belong to.
     *
     * @return Model
     */
    protected function reviewable(): Model
    {
        return $this->episode;
    }
}
