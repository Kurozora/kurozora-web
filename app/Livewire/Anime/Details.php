<?php

namespace App\Livewire\Anime;

use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Models\UserLibrary;
use App\Traits\Livewire\PresentsAlert;
use App\Traits\Livewire\PresentsSubscriptionSheet;
use App\Traits\Livewire\WithReviewBox;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Throwable;

class Details extends Component
{
    use PresentsAlert,
        PresentsSubscriptionSheet,
        WithReviewBox;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The object containing the studio data.
     *
     * @var ?Studio $studio
     */
    public ?Studio $studio;

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
    #[Locked]
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the anime.
     *
     * @var bool $isReminded
     */
    #[Locked]
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    #[Locked]
    public bool $isTracking = false;

    /**
     * Whether to show the video to the user.
     *
     * @var bool $showVideo
     */
    public bool $showVideo = false;

    /**
     * Whether to show the add-to-library modal to the user.
     *
     * @var bool $showAddToLibrary
     */
    public bool $showAddToLibrary = false;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The addition status.
     *
     * @var string
     */
    public string $addStatus = '';

    /**
     * The query strings of the component.
     *
     * @return string[][]
     */
    protected function queryString(): array
    {
        return [
            'addStatus' => ['as' => 'add_to_library', 'except' => ''],
        ];
    }

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

        $this->anime = $anime->loadMissing([
            'genres',
            'languages',
            'media',
            'mediaStat',
            'mediaType',
            'themes',
            'translation',
            'status',
            'tvRating',
            'countryOfOrigin',
            'studios' => function (BelongsToMany $query) {
                $query->withoutGlobalScopes()
                    ->orderByRaw('CASE WHEN is_studio = true THEN 0 ELSE 1 END')
                    ->limit(1);
            },
        ])
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
        $this->studio = $anime->studios->first();

        if ($user = auth()->user()) {
            $this->anime->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $anime->getMorphClass()],
                ['trackable_id', '=', $anime->id],
                ['user_id', '=', $user->id],
            ])->get());

            // Determine whether to show the add-to-library modal
            if ($anime->library->isEmpty()) {
                try {
                    $addStatus = str($this->addStatus)
                        ->title()
                        ->replace('-', '');

                    if (UserLibraryStatus::fromKey($addStatus)) {
                        $this->showAddToLibrary = true;
                    }
                } catch (Exception) {}
            }
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
    public function showTrailerVideo(): void
    {
        $this->showVideo = true;
    }

    /**
     * Add the model to the user's library.
     */
    #[Renderless]
    public function addToLibrary(): void
    {
        $addStatus = str($this->addStatus)
            ->title()
            ->replace('-', '');
        $libraryStatus = UserLibraryStatus::fromKey($addStatus);

        // Update or create the user library entry.
        UserLibrary::withoutSyncingToSearch(function () use($libraryStatus) {
            $userLibrary = UserLibrary::updateOrCreate([
                'user_id' => auth()->id(),
                'trackable_type' => $this->anime->getMorphClass(),
                'trackable_id' => $this->anime->id,
            ], [
                'status' => $libraryStatus->value,
            ]);

            $userLibrary->setRelation('trackable', $this->anime);

            $userLibrary->searchable();
        });

        $this->dispatch('update-library-status', modelType: $this->anime->getMorphClass(), modelID: $this->anime->id, libraryStatus: $libraryStatus->value)
            ->to('components.library-button');
        $this->dismissAddToLibrary();
    }

    /**
     * Handle the dismissing of the add-to-library modal.
     *
     * @return void
     */
    public function dismissAddToLibrary(): void
    {
        $this->showAddToLibrary = false;
        $this->addStatus = '';
    }

    /**
     * Adds the anime to the user's favorite list.
     *
     * @throws Throwable
     */
    public function favoriteAnime(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            DB::transaction(function () use ($user) {
                if ($this->isFavorited) { // Unfavorite the show
                    $user->unfavorite($this->anime);
                } else { // Favorite the show
                    $user->favorite($this->anime);
                }

                $user->bumpStateVersion();
            });

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the anime to the user's reminder list.
     *
     * @throws Throwable
     */
    public function remindAnime(): void
    {
        $user = auth()->user();

        if ($user->is_subscribed) {
            if ($this->isTracking) {
                DB::transaction(function () use ($user) {
                    if ($this->isReminded) { // Don't remind the user
                        $user->unremind($this->anime);
                    } else { // Remind the user
                        $user->remind($this->anime);
                    }

                    $user->bumpStateVersion();
                });

                $this->isReminded = !$this->isReminded;
            } else {
                $this->presentAlert(
                    title: __('Are you tracking?'),
                    message: __('Make sure to add the anime to your library first.')
                );
            }
        } else {
            $this->presentSubscriptionSheet(
                title: __('Integrate with Calendar'),
                message: __('Integrate your anime schedule into your calendar. Never miss an episode again with reminders for new airings.'),
            );
        }
    }

    /**
     * The meta description for this page.
     *
     * @return string
     */
    public function getMetaDescriptionProperty(): string
    {
        $facts = [];

        if ($this->anime->episode_count > 0) {
            $facts[] = trans_choice('{1} :x episode|[2,*] :x episodes', $this->anime->episode_count, ['x' => $this->anime->episode_count]);
        }

        if ($year = $this->anime->started_at?->year) {
            $facts[] = $year;
        }

        if ($this->anime->mediaStat?->rating_average > 0) {
            $facts[] = __('Rated :x/5', ['x' => number_format($this->anime->mediaStat->rating_average, 1)]);
        }

        $summary = array_filter([implode(' · ', $facts), $this->anime->synopsis]);

        return implode(' — ', $summary) ?: __('app.description');
    }

    /**
     * The Schema.org JSON-LD payload for this page.
     *
     * @return array
     */
    public function getSchemaProperty(): array
    {
        return $this->anime->toSchemaOrg();
    }

    /**
     * The breadcrumb chain for this page.
     *
     * @return array
     */
    public function getBreadcrumbProperty(): array
    {
        return $this->anime->schemaBreadcrumbChain();
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
