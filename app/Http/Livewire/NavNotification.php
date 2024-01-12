<?php

namespace App\Http\Livewire;

use Livewire\Component;

class NavNotification extends Component
{
    public bool $isNotificationOpen = false;

    protected $listeners = [
        'is-notifications-open' => 'handleIsNotificationOpen',
    ];

    public function mount(){}

    public function handleIsNotificationOpen(bool $isOpen): void
    {
        $this->isNotificationOpen = $isOpen;
    }

    public function getNotificationsProperty() {
        if (!auth()->check()) {
            return collect();
        }

        return auth()->user()
            ->notifications()
            ->get();
    }

    public function render()
    {
        return view('livewire.nav-notification');
    }
}
