<?php

namespace App\Livewire\Profile\Badges;

use App\Models\User;
use App\Models\UserBadge;
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
     * @var User $badges
     */
    public User $user;

    /**
     * The current page query parameter's alias.
     *
     * @var string $bgc
     */
    public string $bgc = '';

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
        'cursor' => ['as' => 'bgc']
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
     * The user's badge list.
     *
     * @return CursorPaginator
     */
    public function getBadgesProperty(): CursorPaginator
    {
        // We're aliasing `cursorName` as `bgc`, and setting
        // query rule to never show `cursor` param when it's
        // empty. Since `cursor` is also aliased as `bgc` in
        // query rules, and we always keep it empty, as far
        // as Livewire is concerned, `bgc` is also empty. So,
        // `bgc` doesn't show up in the query params in the
        // browser.
        return $this->user->badges()
            ->with(['media'])
            ->orderBy(UserBadge::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate(25, ['*'], 'bgc');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.badges.index');
    }
}
