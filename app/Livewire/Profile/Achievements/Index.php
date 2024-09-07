<?php

namespace App\Livewire\Profile\Achievements;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var User $badges
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
     * The user's achievement list.
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getAchievementsProperty(): LengthAwarePaginator|Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return Badge::achievedUserBadges($this->user)
            ->with('media')
            ->orderBy('is_achieved', 'desc')
            ->orderBy(UserBadge::TABLE_NAME . '.created_at')
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.achievements.index');
    }
}
