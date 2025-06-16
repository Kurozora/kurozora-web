<?php

namespace App\Livewire\Feed;

use App\Models\FeedMessage;
use App\Models\LinkPreview;
use App\Models\User;
use App\Services\LinkPreviewService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var ?User $user
     */
    public ?User $user;

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
        'cursor' => ['as' => 'fmc'],
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'feed-message-reply' => 'reply',
        'feed-message-reShare' => 'reShare',
        'feed-message-share' => 'share',
        'feed-message-edit' => 'edit',
        'feed-message-delete' => 'delete',
        'feed-message-report' => 'report',
    ];

    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The selected popup type.
     *
     * @var string $selectedPopupType
     */
    public string $selectedPopupType = '';

    /**
     * List of acceptable popup types.
     *
     * @var array|string[]
     */
    private array $popupTypes = [
        'edit',
        'share',
        'reShare',
        'reply',
        'delete',
        'report',
    ];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The id of the selected message for popup actions.
     *
     * @var int|null
     */
    public ?int $selectedMessageId = null;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->user = auth()->user();
    }

    /**
     * Boot the component.
     *
     * @param LinkPreviewService $linkPreviewService
     *
     * @return void
     */
    public function boot(LinkPreviewService $linkPreviewService): void
    {
        $this->linkPreviewService = $linkPreviewService;
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
     * Returns the user's feed messages.
     *
     * @return Collection|CursorPaginator
     */
    public function getFeedMessagesProperty(): Collection|CursorPaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return FeedMessage::where('is_reply', '=', false)
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

    function delete($id): void
    {
        $this->selectedPopupType = 'delete';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function confirmDelete(): void
    {
        if ($this->selectedMessageId) {
            $this->user->feed_messages()
                ->where('id', '=', $this->selectedMessageId)
                ->delete();
        }
        $this->closePopup();
    }

    function reply($id): void
    {
        $this->selectedPopupType = 'reply';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function reShare($id): void
    {
        $this->selectedPopupType = 'reShare';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function confirmReShare(): void
    {
        // Implement your reShare logic here
        $this->closePopup();
    }

    function share($id): void
    {
        $this->selectedPopupType = 'share';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function confirmShare(): void
    {
        // Implement your share logic here
        $this->closePopup();
    }

    function edit($id): void
    {
        $this->selectedPopupType = 'edit';
        $this->selectedMessageId = $id;
        $this->message = FeedMessage::find($id)?->content ?? '';
        $this->showPopup = true;
    }

    function confirmEdit(): void
    {
        if ($this->selectedMessageId) {
            FeedMessage::where('id', $this->selectedMessageId)
                ->where('user_id', $this->user->id)
                ->update(['content' => $this->message]);
        }
        $this->closePopup();
    }

    function report($id): void
    {
        $this->selectedPopupType = 'report';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function confirmReport(): void
    {
        // Implement your report logic here
        $this->closePopup();
    }

    function closePopup(): void
    {
        $this->selectedPopupType = '';
        $this->selectedMessageId = null;
        $this->showPopup = false;
        $this->message = '';
    }

    protected LinkPreviewService $linkPreviewService;
    public string $message = '';
    public ?string $detectedLink = null;
    public ?LinkPreview $linkPreview = null;

    public function updatedMessage(): void
    {
        preg_match_all('/https?:\/\/[^\s]+/i', $this->message, $matches);
        $lastLink = last($matches[0]) ?? null;
        if ($lastLink !== $this->detectedLink) {
            $this->detectedLink = $lastLink;
            $this->dispatch('link-detected', ['url' => $lastLink]);
            $this->fetchPreview();
        }
    }

    /**
     * Fetch link previews.
     *
     * @return void
     */
    public function fetchPreview(): void
    {
        if (!$this->detectedLink) {
            $this->linkPreview = null;
            return;
        }

        $this->linkPreview = LinkPreview::where('url', $this->detectedLink)->first();

        if (!$this->linkPreview) {
            try {
                $this->linkPreview = $this->linkPreviewService->resolve($this->detectedLink);
            } catch (Exception $e) {
                $this->linkPreview = null;
            }
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.feed.index');
    }
}
