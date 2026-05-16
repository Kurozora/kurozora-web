<?php

namespace App\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavNotification extends Component
{
    /**
     * Register listeners for the component.
     *
     * @return array
     */
    public function getListeners(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return [
            'echo-private:users.' . auth()->id() . ',.notification.created' => 'onNotificationReceived',
        ];
    }

    /**
     * Refresh the unread indicator when a new push arrives.
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
