<?php

namespace App\Livewire\Feed;

use App\Models\FeedMessage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Activity extends Component
{
    use WithPagination;

    /**
     * The feed message whose activity is being viewed.
     *
     * @var FeedMessage $feedMessage
     */
    public FeedMessage $feedMessage;

    /**
     * Selected tab: `quotes` or `reshares`.
     *
     * @var string $tab
     */
    #[Url(as: 'tab')]
    public string $tab = 'quotes';

    /**
     * Selected sort: `top` or `recent`.
     *
     * @var string $sort
     */
    #[Url(as: 'sort')]
    public string $sort = 'recent';

    /**
     * The list of valid tab values.
     */
    private const array TABS = ['quotes', 'reshares'];

    /**
     * The list of valid sort values.
     */
    private const array SORTS = ['top', 'recent'];

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
        $this->normalize();
    }

    /**
     * Switch the active tab.
     *
     * @param string $tab
     *
     * @return void
     */
    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
        $this->normalize();
        $this->resetPage();
    }

    /**
     * Switch the active sort.
     *
     * @param string $sort
     *
     * @return void
     */
    public function selectSort(string $sort): void
    {
        $this->sort = $sort;
        $this->normalize();
        $this->resetPage();
    }

    /**
     * Toggle a simple re-share of the feed message.
     *
     * @return RedirectResponse|null
     */
    public function toggleSimpleReShare()
    {
        $authUser = auth()->user();

        if ($authUser === null) {
            return to_route('sign-in');
        }

        $deleted = $this->feedMessage->simpleReShares()
            ->where('user_id', '=', $authUser->id)
            ->delete();

        if ($deleted === 0) {
            try {
                FeedMessage::createFor($authUser, [
                    'parent_id' => $this->feedMessage->id,
                    'content' => '',
                    'is_reshare' => true,
                    'is_reply' => false,
                    'is_nsfw' => false,
                    'is_spoiler' => false,
                ]);
            } catch (AuthorizationException $exception) {
                session()->flash('error', $exception->getMessage());
                return;
            }
        }

        $this->feedMessage->refresh();
        $this->resetPage();
    }

    /**
     * Clamp tab/sort to known values.
     *
     * @return void
     */
    private function normalize(): void
    {
        if (!in_array($this->tab, self::TABS, true)) {
            $this->tab = 'quotes';
        }

        if (!in_array($this->sort, self::SORTS, true)) {
            $this->sort = 'recent';
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $authUser = auth()->user();

        if ($this->tab === 'reshares') {
            $query = $this->feedMessage->simpleReShares()
                ->with([
                    'user' => function (BelongsTo $belongsTo) use ($authUser) {
                        $belongsTo->with(['media'])
                            ->withCount(['followers'])
                            ->when($authUser, function ($query, $user) {
                                $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                                    $query->where('user_id', '=', $user->id);
                                }]);
                            });
                    },
                ]);
        } else {
            $query = $this->feedMessage->quoteReShares()
                ->with(FeedMessage::lockupEagerLoads($authUser))
                ->withCount(['replies', 'reShares'])
                ->when($authUser, function ($query, $user) {
                    $query->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        }

        match ($this->sort) {
            'top'   => $query->orderByDesc('ranking_score')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $feedMessages = $query->paginate(25);

        return view('livewire.feed.activity', [
            'feedMessages' => $feedMessages,
        ]);
    }
}
