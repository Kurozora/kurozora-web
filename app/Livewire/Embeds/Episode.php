<?php

namespace App\Livewire\Embeds;

use App\Enums\VideoSource;
use App\Models\Anime;
use App\Models\Video;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Livewire\Component;

class Episode extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var \App\Models\Episode $episode
     */
    public \App\Models\Episode $episode;

    /**
     * The start time of the video.
     *
     * @var int $t
     */
    public $t = 0;

    /**
     * The query strings of the component.
     *
     * * @var string[] $queryString
     */
    protected $queryString = [
        't' => ['except' => 0],
    ];

    /**
     * Prepare the component.
     *
     * @param \App\Models\Episode $episode
     * @return void
     */
    public function mount(\App\Models\Episode $episode): void
    {
        $this->episode = $episode->load([
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough->with([
                    'genres',
                    'studios',
                    'translations',
                    'orderedVideos'
                ]);
            },
            'media',
            'mediaStat',
            'translations',
            'tv_rating',
            'videos',
        ]);
    }

    /**
     * Get the video object.
     *
     * @return Video|null
     */
    public function getVideoProperty(): Video|null
    {
        $videoSource = VideoSource::Default();

        if ($video = $this->episode->videos->firstWhere('source', '=', $videoSource->value)) {
            return $video;
        }

        if ($video = $this->episode->videos->first()) {
            return $video;
        }

        return $this->anime?->orderedVideos->first();
    }

    /**
     * The object containing the anime data.
     *
     * @return Anime|null
     */
    public function getAnimeProperty(): ?Anime
    {
        return $this->episode->anime;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.embeds.episode')
            ->layout('components.layouts.embed');
    }
}
