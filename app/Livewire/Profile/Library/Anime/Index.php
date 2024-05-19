<?php

namespace App\Livewire\Profile\Library\Anime;

use App\Enums\UserLibraryStatus;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The status of the library.
     *
     * @var string $status
     */
    public string $status = '';

    /**
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'status',
    ];

    /**
     * Prepare the component.
     *
     * @param User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;

        $status = str($this->status)->title();
        $status = str_replace('-', '', $status);

        if (!UserLibraryStatus::hasKey($status)) {
            $this->status = 'watching';
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.anime.index');
    }
}
