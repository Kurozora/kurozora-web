<main>
    <x-slot:title>
        {{ $this->title }}
    </x-slot:title>

    <x-slot:description>
        {{ $this->title }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $this->title }}" />
        <meta property="og:description" content="{{ $this->title }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('feed.details', $this->feedMessage) }}">
    </x-slot:meta>

    <x-slot:scripts>
        <script src="{{ url(mix('js/gif.js')) }}"></script>
        <script src="{{ url(mix('js/markdown.js')) }}"></script>
    </x-slot:scripts>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4" wire:ignore>
            <livewire:components.feed.message-lockup :feed-message="$this->feedMessage" :is-detail-page="true" wire:key="{{ uniqid($this->feedMessage->id, true) }}" />
        </section>

        <div class="flex justify-center">
            <x-spinner wire:target="loadPage" />
        </div>

        @if ($this->replies->count())
            <section class="mt-4 border-t border-primary">
                <div class="flex flex-col">
                    @foreach ($this->replies as $feedMessage)
                        <livewire:components.feed.message-lockup :feed-message="$feedMessage" wire:key="{{ uniqid($feedMessage->id, true) }}" />
                    @endforeach
                </div>

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->replies->links() }}
                </div>
            </section>
        @endif
    </div>
</main>
