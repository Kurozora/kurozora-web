<?php

namespace App\Http\Livewire\Profile;

use App\Events\UserViewed;
use App\Models\User;
use App\Models\UserLibrary;
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
     * Returns the user's library.
     *
     * @return LengthAwarePaginator
     */
    public function getUserLibraryProperty(): LengthAwarePaginator
    {
        return UserLibrary::search()
            ->where('user_id', $this->user->id)
            ->paginate(10);
    }

    /**
     * Returns the user's library.
     *
     * @return LengthAwarePaginator
     */
    public function getFavoriteAnimeProperty(): LengthAwarePaginator
    {
        return $this->user
            ->favorite_anime()
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
            ->orderByDesc('created_at')
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
