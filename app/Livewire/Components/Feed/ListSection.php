<?php

namespace App\Livewire\Components\Feed;

use App\Models\FeedMessage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Component;

class ListSection extends Component
{
    /**
     * The cursor for pagination.
     *
     * @var ?string $cursor
     */
    public ?string $cursor = null;

    /**
     * Whether the section is active.
     *
     * @var bool $isActive
     */
    public bool $isActive;

    /**
     * The number of posts to fetch.
     *
     * @var int $count
     */
    public int $count;

    /**
     * Prepare the component.
     *
     * @param ?string $cursor
     * @param bool    $isActive
     * @param int     $count
     *
     * @return void
     */
    public function mount(?string $cursor, bool $isActive, int $count = 25): void
    {
        $this->cursor = $cursor;
        $this->isActive = $isActive;
        $this->count = min($count, 25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     *
     * @throws ConnectionException
     */
    public function render(): Application|Factory|View
    {
        $query = FeedMessage::where('is_reply', '=', false)
            ->orderByDesc('id')
            ->with([
                'user' => function (BelongsTo $belongsTo) {
                    $belongsTo->with(['media']);
                },
                'loveReactant' => function (BelongsTo $query) {
                    $query->with([
                        'reactionCounters',
                        'reactions' => function (HasMany $hasMany) {
                            $hasMany->with(['reacter', 'type']);
                        },
                    ]);
                },
                'linkPreview'
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($this->cursor) {
            $query->where('id', '<', $this->cursor);
        }

        $feedMessages = $query->limit($this->count)->get();
        $hasMore = $feedMessages->count() === $this->count;
        $nextCursor = $feedMessages->last()?->id;

        return view('livewire.components.feed.list-section', [
            'feedMessages' => $feedMessages,
            'hasMore' => $hasMore,
            'nextCursor' => $nextCursor,
        ]);
    }
}
