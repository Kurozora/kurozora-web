<?php

namespace App\Livewire\Anime;

use App\Events\ModelViewed;
use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

    /**
     * The object containing the user's library data.
     *
     * @var Collection|UserLibrary[] $library
     */
    public Collection|array $library;

    /**
     * Whether the user has favorited the anime.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the anime.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

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
        // Call the ModelViewed event
        ModelViewed::dispatch($anime, request()->ip());

        $this->anime = $anime->loadMissing(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'themes', 'translations', 'status', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) use ($anime) {
                return $anime->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            }, function() use ($anime) {
                return $anime;
            });

        if ($user = auth()->user()) {
            $this->anime->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $anime->getMorphClass()],
                ['trackable_id', '=', $anime->id],
                ['user_id', '=', $user->id],
            ])->get());
        } else {
            $this->anime->setRelation('library', collect());
            $this->anime->setRelation('mediaRatings', collect());
        }

        $this->isFavorited = (bool) $anime->isFavorited;
        $this->isReminded = (bool) $anime->isReminded;
        $this->isTracking = $anime->library->isNotEmpty();
        $this->userRating = $anime->mediaRatings;
        $this->library = $anime->library;
    }

    public function dehydrateAnime($value): void
    {
        // For some reason the library relation isn't hydrated correctly.
        // The relation is hydrated without the `where` constraint on the
        // user's ID. So it hydrates all UserLibrary models from the database
        // for the given model. Bad performance. The fix is to unset the
        // relation here, then set it back in the hydrate method.
        $value->unsetRelation('library');
        $value->unsetRelation('mediaRatings');
    }

    public function hydrateAnime($value): void
    {
        $value->setRelation('library', $this->library);
        $value->setRelation('mediaRatings', $this->userRating);
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
     * Shows the trailer video to the user.
     */
    public function showVideo(): void
    {
        $this->showVideo = true;
        $this->showPopup = true;
    }

    /**
     * Adds the anime to the user's favorite list.
     */
    public function favoriteAnime(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->anime);
            } else { // Favorite the show
                $user->favorite($this->anime);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the anime to the user's reminder list.
     */
    public function remindAnime(): void
    {
        $user = auth()->user();

        if ($user->is_subscribed) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->unremind($this->anime);
                } else { // Remind the user
                    $user->remind($this->anime->id);
                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the anime to your library first.'),
                ];
                $this->showPopup = true;
            }
        } else {
            $this->popupData = [
                'title' => __('That’s Unfortunate'),
                'message' => __('This feature is only accessible to pro users 🧐'),
            ];
            $this->showPopup = true;
        }
    }

    /**
     * Returns the studio relationship of the anime.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        if (!$this->readyToLoad) {
            return null;
        }

        return $this->anime->studios()?->firstWhere('is_studio', '=', true) ?? $this->anime->studios->first();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.details');
    }
}
