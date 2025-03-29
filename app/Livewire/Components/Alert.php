<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Alert extends Component
{
    /**
     * Whether to show the alert to the user.
     *
     * @var bool $showAlert
     */
    public bool $showAlert = false;

    /**
     * The data used to populate the alert.
     *
     * @var array|string[]
     */
    public array $alertData = [
        'title' => '',
        'message' => '',
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'present-alert' => 'handlePresentAlert'
    ];

    /**
     * Handles the `present-alert` event.
     *
     * @param string      $title
     * @param null|string $message
     *
     * @return void
     */
    public function handlePresentAlert(string $title, ?string $message): void
    {
        $this->alertData = [
            'title' => $title,
            'message' => $message,
        ];
        $this->showAlert = true;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.alert');
    }
}
