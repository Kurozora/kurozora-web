<?php

namespace App\Livewire\Manga;

use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Models\UserLibrary;
use App\Traits\Livewire\PresentsAlert;
use App\Traits\Livewire\WithReviewBox;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Details extends Component
{
    use PresentsAlert,
        WithReviewBox;

    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

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
     * Whether the user has favorited the manga.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the manga.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the manga.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

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
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($manga, request()->ip());

        $this->manga = $manga->loadMissing(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'themes', 'translation', 'status', 'tv_rating', 'country_of_origin'])
            ->when(auth()->user(), function ($query, $user) use ($manga) {
                return $manga->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
//                        'reminderers as isReminded' => function ($query) use ($user) {
//                            $query->where('user_id', '=', $user->id);
//                        },
                    ]);
            }, function() use ($manga) {
                return $manga;
            });

        if ($user = auth()->user()) {
            $this->manga->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $manga->getMorphClass()],
                ['trackable_id', '=', $manga->id],
                ['user_id', '=', $user->id],
            ])->get());

            // Determine whether to show the add-to-library modal
            if ($manga->library->isEmpty()) {
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
            $this->manga->setRelation('library', collect());
            $this->manga->setRelation('mediaRatings', collect());
        }

        $this->isFavorited = (bool) $manga->isFavorited;
//        $this->isReminded = (bool) $manga->isReminded;
        $this->isTracking = $manga->library->isNotEmpty();
        $this->userRating = $manga->mediaRatings;
        $this->library = $manga->library;
    }

    public function dehydrateManga($value): void
    {
        // For some reason the library relation isn't hydrated correctly.
        // The relation is hydrated without the `where` constraint on the
        // user's ID. So it hydrates all UserLibrary models from the database
        // for the given model. Bad performance. The fix is to unset the
        // relation here, then set it back in the hydrate method.
        $value->unsetRelation('library');
        $value->unsetRelation('mediaRatings');
    }

    public function hydrateManga($value): void
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
                'trackable_type' => $this->manga->getMorphClass(),
                'trackable_id' => $this->manga->id,
            ], [
                'status' => $libraryStatus->value,
            ]);

            $userLibrary->setRelation('trackable', $this->manga);

            $userLibrary->searchable();
        });

        $this->dispatch('update-library-status', modelType: $this->manga->getMorphClass(), modelID: $this->manga->id, libraryStatus: $libraryStatus->value)
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
     * Adds the manga to the user's favorite list.
     */
    public function favoriteManga(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->manga);
            } else { // Favorite the show
                $user->favorite($this->manga);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the manga to the user's reminder list.
     */
    public function remindManga(): void
    {
        $user = auth()->user();

        if ($user->is_subscribed) {
            if ($this->isTracking) {
//                if ($this->isReminded) { // Don't remind the user
//                    $user->reminderManga()->detach($this->manga->id);
//                } else { // Remind the user
//                    $user->reminderManga()->attach($this->manga->id);
//                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->presentAlert(
                    title: __('Are you tracking?'),
                    message: __('Make sure to add the manga to your library first.')
                );
            }
        } else {
            $this->presentAlert(
                title: __('That’s unfortunate'),
                message: __('Reminders are only available to pro and subscribed users 🧐'),
            );
        }
    }

    /**
     * Returns the studio relationship of the manga.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        if (!$this->readyToLoad) {
            return null;
        }

        return $this->manga->studios()?->firstWhere('is_studio', '=', true) ?? $this->manga->studios->first();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.details');
    }
}
