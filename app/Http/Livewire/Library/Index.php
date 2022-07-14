<?php

namespace App\Http\Livewire\Library;

use App\Enums\UserLibraryStatus;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The status of the library.
     *
     * @var string $status
     */
    public string $status = '';

    /**
     * The query strings of the component.
     *
     * @var array $queryString
     */
    protected $queryString = [
        'status',
    ];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $status = str($this->status)->title();
        $status = str_replace('-', '', $status);

        if (!UserLibraryStatus::hasKey($status)) {
            $this->status = strtolower(UserLibraryStatus::Watching()->key);
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.library.index');
    }
}
