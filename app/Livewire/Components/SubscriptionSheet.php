<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SubscriptionSheet extends Component
{
    /**
     * Whether to show the sheet to the user.
     *
     * @var bool $showSheet
     */
    public bool $showSheet = false;

    /**
     * The data used to populate the sheet.
     *
     * @var array
     */
    #[Locked]
    public array $sheetData = [
        'title' => '',
        'message' => '',
        'tipJarEnabled' => false,
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'present-subscription-sheet' => 'handlePresentSubscriptionSheet'
    ];

    /**
     * Handles the `present-subscription-sheet` event.
     *
     * @param string      $title
     * @param null|string $message
     * @param bool        $tipJarEnabled
     *
     * @return void
     */
    public function handlePresentSubscriptionSheet(string $title, ?string $message, bool $tipJarEnabled = false): void
    {
        $this->sheetData = [
            'title' => $title,
            'message' => $message,
            'tipJarEnabled' => $tipJarEnabled,
        ];
        $this->showSheet = true;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.subscription-sheet');
    }
}
