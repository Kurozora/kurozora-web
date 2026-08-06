<?php

namespace App\Livewire;

use App\Models\User;
use App\Traits\Livewire\ListensForUserNotifications;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavigationSidebar extends Component
{
    use ListensForUserNotifications;


    /**
     * The object containing the user data.
     *
     * @var User|null $user
     */
    public ?User $user;

    /**
     * Prepare the component.
     *
     * @param ?User $user
     *
     * @return void
     */
    public function mount($user): void
    {
        $this->user = $user?->loadMissing(['media']);
    }

    /**
     * Register listeners for the component.
     *
     * @return array
     */
    public function getListeners(): array
    {
        return [
            'refresh-navigation-dropdown' => '$refresh',
        ] + $this->userNotificationListeners('onNotificationReceived');
    }

    /**
     * Refresh notification state when a sibling client mutates notifications.
     *
     * @return void
     */
    public function onNotificationReceived(): void
    {
        unset($this->hasUnreadNotifications);
    }

    /**
     * Returns whether the user has any unread notifications.
     *
     * @return bool
     */
    #[Computed]
    public function hasUnreadNotifications(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->unreadNotifications()->exists();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.navigation-sidebar');
    }
}
