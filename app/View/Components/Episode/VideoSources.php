<?php

namespace App\View\Components\Episode;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class VideoSources extends Component
{
    /**
     * The videos the viewer can pick a source from.
     *
     * @var Collection $videos
     */
    public Collection $videos;

    /**
     * Create a new component instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->videos = $model->videos;
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->videos->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.episode.video-sources');
    }
}
