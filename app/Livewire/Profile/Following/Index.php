<?php

namespace App\Livewire\Profile\Following;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\CursorPaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
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
     * @var string $fnc
     */
    public string $fnc = '';

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
        'cursor' => ['as' => 'fnc']
    ];

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
     * The user's following list.
     *
     * @return CursorPaginator
     */
    public function getFollowingsProperty(): CursorPaginator
    {
        // We're aliasing `cursorName` as `fnc`, and setting
        // query rule to never show `cursor` param when it's
        // empty. Since `cursor` is also aliased as `fnc` in
        // query rules, and we always keep it empty, as far
        // as Livewire is concerned, `fnc` is also empty. So,
        // `fnc` doesn't show up in the query params in the
        // browser.
        return $this->user->followedModels()
            ->with(['media'])
            ->withCount(['followers'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate(25, ['*'], 'fnc');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.following.index');
    }
}
