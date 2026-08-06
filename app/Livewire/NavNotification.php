<?php

namespace App\Livewire;

use App\Traits\Livewire\ListensForUserNotifications;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavNotification extends Component
{
    use ListensForUserNotifications;

    /**
     * Register listeners for the component.
     *
     * @return array
     */
    public function getListeners(): array
    {
        return $this->userNotificationListeners('onNotificationReceived');
    }

    /**
     * Refresh the unread indicator when a sibling client mutates notifications.
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
        return view('livewire.nav-notification');
    }
}
