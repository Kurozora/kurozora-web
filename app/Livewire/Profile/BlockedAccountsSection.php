<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class BlockedAccountsSection extends Component
{
    /**
     * The maximum number of preview rows shown in the settings section.
     */
    private const PREVIEW_LIMIT = 5;

    /**
     * The owner of the settings page.
     *
     * @var User $user
     */
    public User $user;

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
     * The first few users blocked by the section's owner.
     *
     * @return Collection
     */
    public function getBlockedUsersProperty(): Collection
    {
        return $this->user->blockedModels()
            ->with(['media'])
            ->orderBy(UserBlock::TABLE_NAME . '.created_at', 'desc')
            ->limit(self::PREVIEW_LIMIT)
            ->get();
    }

    /**
     * The total number of users blocked by the section's owner.
     *
     * @return int
     */
    public function getBlockedCountProperty(): int
    {
        return $this->user->blockedModels()->count();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.blocked-accounts-section');
    }
}
