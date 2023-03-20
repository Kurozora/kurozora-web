<?php

namespace App\Http\Livewire\Profile;

use App\Events\UserViewed;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Details extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The selected popup type.
     *
     * @var string $selectedPopupType
     */
    public string $selectedPopupType = '';

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
     * List of acceptable popup types.
     *
     * @var array|string[]
     */
    private array $popupTypes = [
        'edit',
        'badges',
        'following',
        'followers',
    ];

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        // Call the UserViewed event
        UserViewed::dispatch($user);

        $this->user = $user;
    }

    /**
     * Toggles the popup if the given type is accepted.
     *
     * @param string|null $type
     * @return void
     */
    public function togglePopupFor(?string $type): void
    {
        if (!is_string($type) && !in_array($type, $this->popupTypes)) {
            return;
        }

        $this->selectedPopupType = $type;
        $this->showPopup = true;
    }

    /**
     * Returns the user's anime library.
     *
     * @return LengthAwarePaginator
     */
    public function getUserAnimeLibraryProperty(): LengthAwarePaginator
    {
        return $this->user->whereTracked(Anime::class)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }

    /**
     * Returns the user's manga library.
     *
     * @return LengthAwarePaginator
     */
    public function getUserMangaLibraryProperty(): LengthAwarePaginator
    {
        return $this->user->whereTracked(Manga::class)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }

    /**
     * Returns the user's game library.
     *
     * @return LengthAwarePaginator
     */
    public function getUserGameLibraryProperty(): LengthAwarePaginator
    {
        return $this->user->whereTracked(Game::class)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }

    /**
     * Returns the user's favorited anime.
     *
     * @return LengthAwarePaginator
     */
    public function getFavoriteAnimeProperty(): LengthAwarePaginator
    {
        return $this->user
            ->whereFavorited(Anime::class)
            ->paginate(10);
    }

    /**
     * Returns the user's favorited manga.
     *
     * @return LengthAwarePaginator
     */
    public function getFavoriteMangaProperty(): LengthAwarePaginator
    {
        return $this->user
            ->whereFavorited(Manga::class)
            ->paginate(10);
    }

    /**
     * Returns the user's favorited games.
     *
     * @return LengthAwarePaginator
     */
    public function getFavoriteGamesProperty(): LengthAwarePaginator
    {
        return $this->user
            ->whereFavorited(Game::class)
            ->paginate(10);
    }

    /**
     * Returns the user's feed messages.
     *
     * @return LengthAwarePaginator
     */
    public function getFeedMessagesProperty(): LengthAwarePaginator
    {
        return $this->user->feed_messages()
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.details');
    }
}
