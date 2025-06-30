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

    <x-slot:styles>
        <link rel="preload" href="{{ url(mix('css/watch.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/watch.css')) }}">
    </x-slot:styles>

    <x-slot:scripts>
        <script src="{{ url(mix('js/gif.js')) }}"></script>
        <script src="{{ url(mix('js/markdown.js')) }}"></script>
        <script src="{{ url(mix('js/watch.js')) }}"></script>
    </x-slot:scripts>

    <div class="pb-6" wire:init="loadPage">
        <section class="sticky top-0 pt-4 pb-4 backdrop-blur bg-blur z-10">
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap gap-4 items-center w-full">
                    <x-circle-button aria-label="{{ __('Back') }}" onclick="history.back()">
                        @svg('chevron_backward', 'fill-current', ['width' => '20'])
                    </x-circle-button>

                    <div class="flex flex-col">
                        <h1 class="text-2xl font-bold">{{ __('Post') }}</h1>
                        <p class="text-secondary">{{ trans_choice('{1} :x reply|[2,*] :x replies', $this->feedMessage->replies_count, ['x' => $this->feedMessage->replies_count]) }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    @hasrole('superAdmin')
                    <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </div>
            </div>

            <div>

            </div>
        </section>

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
        @elseif ($readyToLoad)
            <section class="flex flex-col items-center justify-center mt-4 text-center" style="min-height: 50vh;">
                @svg('bubble_left_and_bubble_right_fill', 'fill-current w-40')

                <p class="font-bold">{{ __('No Replies') }}</p>

                <p class="text-sm text-secondary">{{ __('Be the first to reply to this message!') }}</p>

                <x-button class="mt-2">{{ __('Reply to :x', ['x' => $this->feedMessage->user->username]) }}</x-button>
            </section>
        @endif
    </div>
</main>
