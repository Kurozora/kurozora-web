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
     * Returns the feed message rendered in the lockup body.
     *
     * @return FeedMessage
     */
    public function getDisplayMessageProperty(): FeedMessage
    {
        if ($this->isSimpleReShareEnvelope()) {
            return $this->feedMessage->parentMessage;
        }

        return $this->feedMessage;
    }

    /**
     * Whether the envelope is a simple re-share of a parent message.
     *
     * @return bool
     */
    private function isSimpleReShareEnvelope(): bool
    {
        return $this->feedMessage->is_reshare
            && trim((string) $this->feedMessage->content) === ''
            && $this->feedMessage->parentMessage !== null;
    }

    /**
     * Toggle the like status of the feed message.
     *
     * @return void
     */
    public function toggleLike(): void
    {
        auth()->user()->toggleHeart($this->displayMessage);
    }

    /**
     * Toggle a simple re-share of the feed message.
     *
     * @return void
     */
    public function toggleSimpleReShare(): void
    {
        if ($this->displayMessage->isReShared) {
            $this->dispatch('feed-message-undo-reshare', id: $this->displayMessage->id);
        } else {
            $this->dispatch('feed-message-simple-reshare', id: $this->displayMessage->id);
        }
    }

    /**
     * Open the quote composer for the feed message.
     *
     * @return void
     */
    public function quote(): void
    {
        $this->dispatch('feed-message-quote', id: $this->displayMessage->id);
    }

    /**
     * Reply to a feed message.
     *
     * @return void
     */
    public function reply(): void
    {
        $this->dispatch('feed-message-reply', id: $this->displayMessage->id);
    }

    /**
     * Share a feed message.
     *
     * @return void
     */
    public function share(): void
    {
        $this->dispatch('feed-message-share', id: $this->displayMessage->id);
    }

    /**
     * Edit a feed message.
     *
     * @return void
     */
    public function edit(): void
    {
        $this->dispatch('feed-message-edit', id: $this->displayMessage->id);
    }

    /**
     * Delete a feed message.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->dispatch('feed-message-delete', id: $this->displayMessage->id);
    }

    /**
     * Report a feed message.
     *
     * @return void
     */
    public function report(): void
    {
        $this->dispatch('feed-message-report', id: $this->displayMessage->id);
    }

    /**
     * Returns the link preview for the displayed message, if any.
     *
     * @return ?LinkPreview
     */
    public function getLinkPreviewProperty(): ?LinkPreview
    {
        return $this->displayMessage->linkPreview;
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
