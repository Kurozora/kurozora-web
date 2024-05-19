<?php

namespace App\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'is-notifications-open' => 'handleIsNotificationOpen',
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {}

    /**
     * Handle the `is-notifications-open` event.
     *
     * @param bool $isOpen
     *
     * @return void
     */
    public function handleIsNotificationOpen(bool $isOpen): void
    {
        $this->isNotificationOpen = $isOpen;
    }

    /**
     * Returns the list of user's notifications.
     *
     * @return Collection
     */
    public function getNotificationsProperty(): Collection
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.nav-notification');
    }
}
