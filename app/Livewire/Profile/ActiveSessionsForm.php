<?php

namespace App\Livewire\Profile;

use App\Traits\InteractsWithSessions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ActiveSessionsForm extends Component
{
    use InteractsWithSessions;

    /**
     * The maximum number of other sessions shown in the settings preview.
     */
    private const int PREVIEW_LIMIT = 4;

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $otherSessions = $this->otherSessionViews();

        return view('livewire.profile.active-sessions-form', [
            'currentSession' => $this->currentSessionView(),
            'previewSessions' => $otherSessions->take(self::PREVIEW_LIMIT),
            'otherCount' => $otherSessions->count(),
        ]);
    }
}
