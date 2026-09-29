<x-base-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:description>
        {{ $title }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $title }}" />
        <meta property="og:description" content="{{ $title }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('feed.details', $feedMessage) }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/gif.js', 'resources/js/markdown.js', 'resources/js/watch.js'])
    </x-slot:scripts>

    <main>
        <div class="pb-6 xl:safe-area-inset">
            <section class="sticky top-0 pt-4 pb-4 backdrop-blur bg-blur z-10">
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap gap-4 items-center w-full">
                        <x-circle-button aria-label="{{ __('Back') }}" onclick="historyManager.back('{{ route('feed.index') }}')">
                            @svg('chevron_backward', 'fill-current', ['width' => '20'])
                        </x-circle-button>

                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold">{{ __('Post') }}</h1>
                            <p class="text-secondary">{{ trans_choice('{1} :x reply|[2,*] :x replies', $feedMessage->replies_count, ['x' => $feedMessage->replies_count]) }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <div>

                </div>
            </section>

            <section class="mb-4">
                <x-feed.message-lockup :feed-message="$feedMessage" :is-detail-page="true" />
            </section>

            <section class="pb-4 border-b border-primary">
                <a livewire:navigate href="{{ route('profile.details', $feedMessage->user) }}" class="pl-4 pr-4 text-secondary text-sm">{!! __('Replying to <span class="text-tint">@:x</span>', ['x' => $feedMessage->user->slug]) !!}</a>

                <div class="flex flex-row gap-2 mt-4 pl-4 pr-4">
                    <x-profile-image-view class="w-12 h-12" :user="auth()->user()" />

                    <div class="flex flex-col gap-2 w-full">
                        <livewire:components.feed-message-composer is-reply="true" />
                    </div>
                </div>
            </section>

            @if ($replies->count())
                <section class="mt-4 border-t border-primary" data-paginated="replies">
                    <div class="flex flex-col">
                        @foreach ($replies as $reply)
                            <x-feed.message-lockup :feed-message="$reply" />
                        @endforeach
                    </div>

                    <div class="mt-4 pl-4 pr-4">
                        {{ $replies->links() }}
                    </div>
                </section>
            @else
                <x-empty-state icon="bubble_left_and_bubble_right_fill" :heading="__('No Replies')" :description="__('Be the first to reply to this message!')" />
            @endif
        </div>

        <x-feed.message-modals />
    </main>
</x-base-layout>
