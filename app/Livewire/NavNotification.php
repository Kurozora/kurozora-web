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
     * The latest notification id.
     *
     * @var string|null
     */
    public ?string $latestNotificationId = null;

    /**
     * Whether there are new notifications.
     *
     * @var bool
     */
    public bool $newNotifications = false;

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
        $this->newNotifications = false;
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

        $notifications = auth()->user()
            ->notifications()
            ->with(['notifier'])
            ->get();
        $this->latestNotificationId = $notifications->last->id;
        return $notifications;
    }

    /**
     * Polls for new notifications.
     *
     * @return void
     */
    public function pollForNewNotifications(): void
    {
        $notifications = auth()->user()
            ->notifications()
            ->when($this->latestNotificationId, function ($query) {
                return $query->where('id', '>', $this->latestNotificationId);
            })
            ->orderBy('id')
            ->exists();

        if ($notifications) {
            $this->newNotifications = $notifications;
            $this->setLatestNotificationId();
        }
    }

    /**
     * Sets the latest notification id.
     *
     * @return void
     */
    function setLatestNotificationId(): void
    {
        $this->latestNotificationId = auth()->user()
            ->notifications()
            ->max('id');
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
