<?php

namespace App\Http\Livewire\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ChatBox extends Component
{
    /**
     * Comment string.
     *
     * @var string $comment
     */
    public string $comment = '';

    /**
     * Selected chat option.
     *
     * @var string $selectedChatOption
     */
    public string $selectedChatOption = '';

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.chat-box');
    }
}
