<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class SubscribeToReminders extends Component
{
    /**
     * Redirect the user to the WebCal subscription URL.
     *
     * @return void
     */
    public function subscribeToReminders(): void
    {
        $this->redirect(str(route('api.me.reminders.download'))->replace(['https', 'http'], 'webcal'));
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.subscribe-to-reminders');
    }
}
