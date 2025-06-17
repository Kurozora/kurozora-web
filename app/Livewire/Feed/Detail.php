<?php

namespace App\Livewire\Feed;

use App\Models\FeedMessage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Detail extends Component
{
    use WithPagination;

    /**
     * The object containing the feed message data.
     *
     * @var FeedMessage $feedMessage
     */
    public FeedMessage $feedMessage;

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
     * @param FeedMessage $feedMessage
     *
     * @return void
     */
    public function mount(FeedMessage $feedMessage): void
    {
        $this->feedMessage = $feedMessage;
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Returns the feed message's replies.
     *
     * @return Collection|CursorPaginator
     */
    public function getRepliesProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->feedMessage->replies()
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
            ->cursorPaginate(25, ['*'], 'fmc');
    }

    /**
     * Returns the page's title.
     *
     * @return string
     */
    public function getTitleProperty(): string
    {
        $author = $this->feedMessage->user->username;
        $content = $this->feedMessage->content;

        return __(':author on :app: ":content" :url', [
            'author' => $author,
            'app' => config('app.name'),
            'content' => $content,
            'url' => url()->current(),
        ]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.feed.detail');
    }
}
