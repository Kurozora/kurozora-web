<?php

namespace App\Livewire\Episode;

use App\Enums\VideoSource;
use App\Events\ModelViewed;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\MediaRating;
use App\Models\Season;
use App\Models\Video;
use App\Traits\Livewire\WithReviewBox;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

    /**
     * The object containing the episode data.
     *
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

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
     * @var int $timestamp
     */
    public $timestamp = 0;

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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The query strings of the component.
     *
     * * @var string[] $queryString
     */
    protected $queryString = [
        'timestamp' => ['except' => 0, 'as' => 't'],
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'preferredVideoSourceChanged' => 'selectPreferredSource'
    ];

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($episode, request()->ip());

        $this->episode = $episode->loadMissing([
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough
                    ->withoutGlobalScopes()
                    ->with([
                        'studios',
                        'translations',
                        'orderedVideos'
                    ]);
            },
            'media',
            'mediaStat',
            'season' => function ($query) {
                $query->with(['media', 'translations']);
            },
            'translations',
            'tv_rating',
        ])
            ->when(auth()->user(), function ($query, $user) use ($episode) {
                return $episode->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            }, function () use ($episode) {
                return $episode;
            });

        if (!auth()->check()) {
            $this->episode->setRelation('mediaRatings', collect());
        }

        $this->userRating = $episode->mediaRatings;
        $this->setupActions();
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions(): void
    {
        $user = auth()->user();

        if (!empty($user)) {
            $this->isTracking = $user->hasTracked($this->anime);
            $this->isReminded = $user->hasReminded($this->anime);
        }
    }

    /**
     * Shows the trailer video to the user.
     *
     * @return void
     */
    public function showTrailerVideo(): void
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
    public function getVideoProperty(): ?Video
    {
        if (!$this->readyToLoad) {
            return null;
        }

        $videoSource = VideoSource::fromKey($this->preferredVideoSource);

        if ($video = $this->episode->videos->firstWhere('source', '=', $videoSource->value)) {
            $episode = $this->episode->withoutRelations();
            $episode = $episode->setRelation('media', $this->episode->media);

            return $video->setRelation('videoable', $episode);
        }

        if ($video = $this->episode->videos->first()) {
            $this->selectPreferredSource($video->source->key);
            $episode = $this->episode->withoutRelations();
            $episode = $episode->setRelation('media', $this->episode->media);

            return $video->setRelation('videoable', $episode);
        }

        $anime = $this->anime->withoutRelations();
        $anime = $anime->setRelation('media', $this->anime->media);

        return $this->anime->orderedVideos->first()
            ?->setRelation('videoable', $anime);
    }

    /**
     * Adds the anime to the user's reminder list.
     */
    public function remindAnime(): void
    {
        $user = auth()->user();

        if ($user->is_pro) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->unremind($this->anime);
                } else { // Remind the user
                    $user->remind($this->anime);
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.episode.details');
    }
}
