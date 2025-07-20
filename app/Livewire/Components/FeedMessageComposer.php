<?php

namespace App\Livewire\Components;

use App\Models\FeedMessage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FeedMessageComposer extends Component
{
    use WithFileUploads;

    /**
     * The content of the message.
     *
     * @var string|null $content
     */
    #[Validate]
    public ?string $content;

    /**
     * The character limit for the message.
     *
     * @var int $charLimit
     */
    public int $charLimit;

    #[Validate]
    public $attachments = []; // temporary uploads
    public $meta = []; // [{ spoiler, nsfw, alt }]

    /**
     * Whether the message is a reply.
     *
     * @var bool $isReply
     */
    public bool $isReply;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'content' => ['bail', 'string', 'min:1', 'max:' . FeedMessage::maxContentLength()],
            'attachments.*' => 'image|max:2048'
        ];
    }

    /**
     * Prepare the component.
     *
     * @param bool $isReply
     *
     * @return void
     */
    public function mount(bool $isReply): void
    {
        $this->isReply = $isReply;
        $this->charLimit = FeedMessage::maxContentLength();
    }

    public function addAttachment($file): void
    {
        if (count($this->attachments) < 4) {
            $this->attachments[] = $file;
            $this->meta[] = ['spoiler' => false, 'nsfw' => false, 'alt' => ''];
        }
    }

    public function toggleMeta($idx, $field): void
    {
        $this->meta[$idx][$field] = !$this->meta[$idx][$field];
    }

    public function updatedMeta($idx, $field, $value): void
    {
        $this->meta[$idx][$field] = $value;
    }

    public function removeAttachment($idx): void
    {
        unset($this->attachments[$idx], $this->meta[$idx]);
        $this->attachments = array_values($this->attachments);
        $this->meta = array_values($this->meta);
    }

    public function submit()
    {
        if (!auth()->check()) {
            return to_route('sign-in');
        }

        $this->validate();

        auth()->user()->feed_messages()
            ->create([
                'parent_feed_message_id' => null,
                'content' => $this->content,
                'is_nsfw' => false,
                'is_reply' => false,
                'is_reshare' => false,
                'is_spoiler' => false,
            ]);

        $this->dispatch('postSubmitted', ['body' => $this->body, 'attachments' => $this->attachments, 'meta' => $this->meta]);
    }

    public function render()
    {
        return view('livewire.components.feed-message-composer');
    }
}
