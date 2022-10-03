<?php

namespace App\Http\Livewire\Episode;

use App\Enums\VideoSource;
use App\Events\EpisodeViewed;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Video;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

    /**
     * Whether the user is reminded of the anime.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * The user's preferred video source.
     *
     * @var string
     */
    public $preferredVideoSource = 'Default';

    /**
     * The start time of the video.
     *
     * @var int $t
     */
    public $t = 0;

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
     * Whether to show the share popup to the user.
     *
     * @var bool $showSharePopup
     */
    public bool $showSharePopup = false;

    /**
     * The query strings of the component.
     *
     * * @var string[] $queryString
     */
    protected $queryString = [
        't' => ['except' => 0],
    ];

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    public array $popupData = [
        'title' => '',
        'message' => '',
        'type' => 'default'
    ];

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode): void
    {
        // Call the EpisodeViewed event
        EpisodeViewed::dispatch($episode);

        $this->episode = $episode;
        $this->setupActions();
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions()
    {
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->isTracking($this->anime);
            $this->isReminded = $user->reminder_anime()->where('anime_id', $this->anime->id)->exists();
        }
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
     * Adds the anime to the user's reminder list.
     */
    public function remindAnime()
    {
        $user = auth()->user();

        if ($user->isPro()) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->reminder_anime()->detach($this->anime->id);
                } else { // Remind the user
                    $user->reminder_anime()->attach($this->anime->id);
                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the anime to your library first.'),
                    'type' => 'default',
                ];
                $this->showPopup = true;
            }
        } else {
            $this->popupData = [
                'title' => __('That’s Unfortunate'),
                'message' => __('This feature is only accessible to pro users 🧐'),
                'type' => 'default',
            ];
            $this->showPopup = true;
        }
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
     * The object containing the season data.
     *
     * @return Season|null
     */
    public function getSeasonProperty(): ?Season
    {
        return $this->episode->season;
    }

    /**
     * Get the next episode.
     *
     * @return Episode|null
     */
    public function getNextEpisodeProperty(): Episode|null
    {
        return $this->episode->next_episode;
    }

    /**
     * A list of episode suggestions based on current episode.
     *
     * @return LengthAwarePaginator
     */
    public function getSuggestedEpisodesProperty(): LengthAwarePaginator
    {
        return Episode::search(utf8_encode(substr($this->episode->title, 0, 20)))
            ->paginate(10);
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
