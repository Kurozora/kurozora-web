<?php

namespace App\View\Components\Feed;

use App\Models\FeedMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MessageList extends Component
{
    /**
     * The number of messages per page.
     *
     * @var int PER_PAGE
     */
    const int PER_PAGE = 25;

    /**
     * Whether only a page of messages is rendered.
     *
     * @var bool $fragment
     */
    public bool $fragment;

    /**
     * The messages on this page.
     *
     * @var Collection $feedMessages
     */
    public Collection $feedMessages;

    /**
     * The id to load older messages from.
     *
     * @var int|null $nextCursor
     */
    public ?int $nextCursor;

    /**
     * The id of the newest message the page knows about.
     *
     * @var int|null $latestId
     */
    public ?int $latestId;

    /**
     * Create a new component instance.
     *
     * @param int|null $cursor
     * @param int|null $after
     * @param bool     $fragment
     */
    public function __construct(?int $cursor = null, ?int $after = null, bool $fragment = false)
    {
        $this->fragment = $fragment;
        $this->feedMessages = $this->loadFeedMessages($cursor, $after);
        $this->nextCursor = $after === null && $this->feedMessages->count() === self::PER_PAGE
            ? $this->feedMessages->last()->id
            : null;
        $this->latestId = $after === null
            ? FeedMessage::where('is_reply', '=', false)->max('id')
            : $this->feedMessages->first()?->id;
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.feed.message-list');
    }

    /**
     * Load a page of messages older than the cursor or newer than the given id.
     *
     * @param int|null $cursor
     * @param int|null $after
     *
     * @return Collection
     */
    protected function loadFeedMessages(?int $cursor, ?int $after): Collection
    {
        $authUser = auth()->user();

        return FeedMessage::where('is_reply', '=', false)
            ->when($cursor !== null, fn ($query) => $query->where('id', '<', $cursor))
            ->when($after !== null, fn ($query) => $query->where('id', '>', $after))
            ->orderByDesc('id')
            ->with(FeedMessage::lockupEagerLoads($authUser))
            ->withCount(['replies', 'reShares'])
            ->when($authUser, function ($query, $user) {
                $query->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->limit(self::PER_PAGE)
            ->get();
    }
}
