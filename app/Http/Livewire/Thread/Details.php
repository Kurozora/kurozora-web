<?php

namespace App\Http\Livewire\Thread;

use App\Models\ForumThread;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the thread data.
     *
     * @var ?ForumThread $thread
     */
    public ?ForumThread $thread;

    /**
     * The page's data.
     *
     * @var array
     */
    public array $page = [
        'title' => '',
        'type' => 'website',
    ];

    /**
     * Prepare the component.
     *
     * @param ForumThread $thread
     *
     * @return void
     */
    public function mount(ForumThread $thread)
    {
        $this->thread = $thread;

        $this->page['title'] = $thread->title;
    }

    /**
     * Render the component.
     *
     * @return Factory|View|Application
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.thread.details')
            ->layout('layouts.base');
    }
}
