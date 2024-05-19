<?php

namespace App\Livewire\Profile\Followers;

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
     * The current page parameter's alias.
     *
     * @var string $frc
     */
    public string $frc = '';

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
        'cursor' => ['as' => 'frc']
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
     * The user's followers list.
     *
     * @return CursorPaginator
     */
    public function getFollowersProperty(): CursorPaginator
    {
        // We're aliasing `cursorName` as `frc`, and setting
        // query rule to never show `cursor` param when it's
        // empty. Since `cursor` is also aliased as `frc` in
        // query rules, and we always keep it empty, as far
        // as Livewire is concerned, `frc` is also empty. So,
        // `frc` doesn't show up in the query params in the
        // browser.
        return $this->user->followers()
            ->with(['media'])
            ->withCount(['followers'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate(25, ['*'], 'frc');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.followers.index');
    }
}
