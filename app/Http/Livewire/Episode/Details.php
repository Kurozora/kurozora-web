<?php

namespace App\Http\Livewire\Episode;

use App\Enums\VideoSource;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Video;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * The object containing the season data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The user's preferred video source.
     *
     * @var string
     */
    public $preferredVideoSource = 'Default';

    /**
     * Whether to show the video to the user.
     *
     * @var bool $showVideo
     */
    public bool $showVideo = false;

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
    ];

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode): void
    {
        $this->episode = $episode;
        $this->season = $this->episode->season;
        $this->anime = $this->season->anime;
    }

    /**
     * Shows the trailer video to the user.
     *
     * @return void
     */
    public function showVideo(): void
    {
        $this->showVideo = true;
    }

    /**
     * Select the preferred video source.
     *
     * @param string $source
     * @return void
     */
    public function selectPreferredSource(string $source): void
    {
        $this->preferredVideoSource = $source;
    }

    /**
     * Get the video object.
     *
     * @return Video|null
     * @throws InvalidEnumKeyException
     */
    public function getVideoProperty(): Video|null
    {
        $videoSource = VideoSource::fromKey($this->preferredVideoSource);

        if ($video = $this->episode->videos()->firstWhere('source', $videoSource->value)) {
            return $video;
        }

        if ($video = $this->episode->videos()->first()) {
            $this->selectPreferredSource($video->source->key);
            return $video;
        }

        return $this->anime->orderedVideos()->first();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.episode.details');
    }
}
