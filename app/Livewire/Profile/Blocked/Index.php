<?php

namespace App\Livewire\Profile\Blocked;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        abort_if($user->id !== auth()->id(), 403);

        $this->user = $user;
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
     * The auth user's blocked users list.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getBlockedUsersProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->user->blockedModels()
            ->with(['media'])
            ->withCount(['followers'])
            ->orderBy(UserBlock::TABLE_NAME . '.created_at', 'desc')
            ->paginate(25);
    }

    /**
     * Unblock the given user.
     *
     * @param int $userID
     *
     * @return void
     */
    public function unblock(int $userID): void
    {
        $blockedUser = User::find($userID);

        if ($blockedUser === null) {
            return;
        }

        auth()->user()->unblock($blockedUser);

        $this->resetPage();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.blocked.index');
    }
}
