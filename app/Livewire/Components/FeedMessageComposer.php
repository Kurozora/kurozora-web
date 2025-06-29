<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;

class FeedMessageComposer extends Component
{
    use WithFileUploads;

    public ?string $body;
    public $charLimit = 500; // dynamic limit
    public $attachments = []; // temporary uploads
    public $meta = []; // [{ spoiler, nsfw, alt }]

    protected $rules = [
        'body' => 'max:1000',
        'attachments.*' => 'image|max:2048',
    ];

    public function updatedBody(): void
    {
        if (strlen($this->body) > $this->charLimit) {
            $this->body = substr($this->body, 0, $this->charLimit);
        }
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

    public function submit(): void
    {
        $this->validate();
        // store attachments and post...
        $this->dispatch('postSubmitted', ['body' => $this->body, 'attachments' => $this->attachments, 'meta' => $this->meta]);
    }

    public function render()
    {
        return view('livewire.components.feed-message-composer');
    }
}
