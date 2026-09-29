<?php

namespace App\View\Components\User;

use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FeedMessagesSection extends Component
{
    /**
     * The user whose feed messages are shown.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The current page of feed messages.
     *
     * @var CursorPaginator $feedMessages
     */
    public CursorPaginator $feedMessages;

    /**
     * Create a new component instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->refreshUrl = route('profile.section', [$user, 'feed-messages', 'fmc' => request()->query('fmc')], false);
        $this->feedMessages = $this->loadFeedMessages();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->feedMessages->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.user.feed-messages-section');
    }

    /**
     * Load the current page of feed messages.
     *
     * @return CursorPaginator
     */
    protected function loadFeedMessages(): CursorPaginator
    {
        $authUser = auth()->user();

        return $this->user->feedMessages()
            ->with(FeedMessage::lockupEagerLoads($authUser))
            ->withCount(['replies', 'reShares'])
            ->when($authUser, function ($query, $user) {
                $query->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(25, ['*'], 'fmc');
    }
}
