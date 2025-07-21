<?php

namespace App\Livewire\Feed;

use App\Models\FeedMessage;
use App\Models\LinkPreview;
use App\Models\User;
use App\Services\LinkPreviewService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
        'load-more' => 'loadMore'
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
     * The sections of the feed.
     *
     * @var array
     */
    public array $sections = [];

    /**
     * The new sections of the feed.
     *
     * @var array
     */
    public array $newSections = [];

    /**
     * The key of the active section.
     *
     * @var int|null
     */
    public ?int $activeSectionKey = null;

    /**
     * The latest feed message id.
     *
     * @var int|null
     */
    public ?int $latestFeedMessageId = null;

    /**
     * The count of new feed messages.
     *
     * @var int
     */
    public int $newFeedMessagesCount = 0;

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
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->user = auth()->user();
        $this->sections[] = ['type' => 'messages', 'cursor' => null];
        $this->activeSectionKey = 0;
        $this->setLatestFeedMessageId();
    }

    /**
     * Sets the latest feed message id.
     *
     * @return void
     */
    function setLatestFeedMessageId(): void
    {
        $this->latestFeedMessageId = FeedMessage::where('is_reply', '=', false)
            ->max('id');
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
     * Loads the next page of feed messages.
     *
     * @param int $cursor
     *
     * @return void
     */
    public function loadMore(int $cursor): void
    {
        $this->activeSectionKey++;
        $this->sections[] = ['type' => 'messages', 'cursor' => $cursor];
    }

    /**
     * Polls for new feed messages.
     *
     * @return void
     */
    public function pollForNewFeedMessages(): void
    {
        $feedMessages = FeedMessage::where('is_reply', '=', false)
            ->where('id', '>', $this->latestFeedMessageId)
            ->orderBy('id')
            ->count();

        if ($feedMessages) {
            $this->newFeedMessagesCount = $feedMessages;
            $this->setLatestFeedMessageId();
        }
    }

    /**
     * Shows the new feed messages section.
     *
     * @return void
     */
    public function showNewFeedMessages(): void
    {
        $this->newSections[] = ['type' => 'messages', 'cursor' => null, 'count' => $this->newFeedMessagesCount];

        $this->newFeedMessagesCount = 0;
        $this->setLatestFeedMessageId();
    }

    /**
     * Deletes a feed message.
     *
     * @param int $id
     *
     * @return void
     */
    function delete(int $id): void
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

    function reply(int $id): void
    {
        $this->selectedPopupType = 'reply';
        $this->selectedMessageId = $id;
        $this->showPopup = true;
    }

    function reShare(int $id): void
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

    function share(int $id): void
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

    function edit(int $id): void
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

    function report(int $id): void
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
