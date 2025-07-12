<?php

namespace App\Livewire\Components\Feed;

use App\Models\FeedMessage;
use App\Models\LinkPreview;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class MessageLockup extends Component
{
    /**
     * The object containing the feed message data.
     *
     * @var FeedMessage $feedMessage
     */
    public FeedMessage $feedMessage;

    /**
     * Whether the component is on a detail page.
     *
     * @var bool $isDetailPage
     */
    public bool $isDetailPage;

    /**
     * Prepare the component.
     *
     * @param FeedMessage $feedMessage
     * @param bool        $isDetailPage
     *
     * @return void
     */
    public function mount(FeedMessage $feedMessage, bool $isDetailPage = false): void
    {
        $this->feedMessage = $feedMessage;
        $this->isDetailPage = $isDetailPage;
    }

    /**
     * Hydrate the feed message.
     *
     * @return void
     */
    public function hydrateFeedMessage(): void
    {
        $this->feedMessage->loadCount(['replies', 'reShares']);
    }

    /**
     * Toggle the like status of the feed message.
     *
     * @return void
     */
    public function toggleLike(): void
    {
        auth()->user()->toggleHeart($this->feedMessage);
    }

    /**
     * Reshare a feed message.
     *
     * @return void
     */
    public function reShare(): void
    {
        $this->dispatch('feed-message-reShare', id: $this->feedMessage->id);
    }

    /**
     * Reply to a feed message.
     *
     * @return void
     */
    public function reply(): void
    {
        $this->dispatch('feed-message-reply', id: $this->feedMessage->id);
    }

    /**
     * Share a feed message.
     *
     * @return void
     */
    public function share(): void
    {
        $this->dispatch('feed-message-share', id: $this->feedMessage->id);
    }

    /**
     * Edit a feed message.
     *
     * @return void
     */
    public function edit(): void
    {
        $this->dispatch('feed-message-edit', id: $this->feedMessage->id);
    }

    /**
     * Delete a feed message.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->dispatch('feed-message-delete', id: $this->feedMessage->id);
    }

    /**
     * Report a feed message.
     *
     * @return void
     */
    public function report(): void
    {
        $this->dispatch('feed-message-report', id: $this->feedMessage->id);
    }

    public function getLinkPreviewProperty(): ?LinkPreview
    {
        return $this->feedMessage->linkPreview;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.feed.message-lockup');
    }
}
