<?php

namespace App\Livewire\Components\User;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;
use Livewire\WithPagination;

#[Isolate]
class FeedMessagesSection extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The current page query parameter's alias.
     *
     * @var string $bgc
     */
    public string $fmc = '';

    /**
     * The current page query parameter.
     *
     * @var string $cursor
     */
    public string $cursor = '';

    /**
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'cursor' => ['as' => 'fmc']
    ];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     *
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Returns the user's feed messages.
     *
     * @return Collection|CursorPaginator
     */
    public function getFeedMessagesProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->user->feed_messages()
            ->with([
                'user' => function (BelongsTo $belongsTo) {
                    $belongsTo->with(['media']);
                },
                'loveReactant' => function (BelongsTo $query) {
                    $query->with([
                        'reactionCounters',
                        'reactions' => function (HasMany $hasMany) {
                            $hasMany->with(['reacter', 'type']);
                        }
                    ]);
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(25,  ['*'], 'fmc');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.user.feed-messages-section');
    }
}
