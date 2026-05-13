<?php

namespace App\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NavNotification extends Component
{
    /**
     * Whether the notification dropdown is open.
     *
     * @var bool $isNotificationOpen
     */
    public bool $isNotificationOpen = false;

    /**
     * Register the Echo notification listener for the authenticated user.
     *
     * @return array
     */
    public function getListeners(): array
    {
        if (!auth()->check()) {
            return [];
        }

        return [
            'echo-notification:App.Models.User.' . auth()->id() => 'onNotificationReceived',
        ];
    }

    /**
     * Refresh notification state when a new push arrives.
     *
     * @return void
     */
    public function onNotificationReceived(): void
    {
        unset($this->notifications);
        unset($this->hasUnreadNotifications);
    }

    /**
     * Open the dropdown and mark unread notifications as read.
     *
     * @return void
     */
    public function openDropdown(): void
    {
        $this->isNotificationOpen = true;
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
        unset($this->notifications, $this->hasUnreadNotifications);
    }

    /**
     * Returns the list of user's notifications.
     *
     * @return Collection
     */
    #[Computed]
    public function notifications(): Collection
    {
        if (!auth()->check()) {
            return collect();
        }

        return auth()->user()
            ->notifications()
            ->with(['notifier'])
            ->get();
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
