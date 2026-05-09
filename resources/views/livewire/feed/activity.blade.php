<main>
    <x-slot:title>
        {{ __('Post activity') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Quotes and re-shares of this post.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Post activity') }}" />
        <meta property="og:description" content="{{ __('Quotes and re-shares of this post.') }}" />
        <meta property="og:type" content="website" />
        <meta name="robots" content="noindex" />
        <link rel="canonical" href="{{ route('feed.activity', $feedMessage) }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css'])
    </x-slot:styles>

    <div class="pb-6 xl:safe-area-inset">
        <section class="sticky top-0 pt-4 pb-4 backdrop-blur bg-blur z-10">
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap gap-4 items-center w-full">
                    <x-circle-button aria-label="{{ __('Back') }}" onclick="historyManager.back('{{ route('feed.details', $feedMessage) }}')">
                        @svg('chevron_backward', 'fill-current', ['width' => '20'])
                    </x-circle-button>

                    <div class="flex flex-col">
                        <h1 class="text-2xl font-bold">{{ __('Post activity') }}</h1>
                    </div>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    <x-dropdown align="right" width="48">
                        <x-slot:trigger>
                            <x-square-button title="{{ __('Sort') }}">
                                @svg('arrow_up_arrow_down_circle', 'fill-current', ['width' => '18'])
                                <p class="ml-2 mr-2">{{ $sort === 'top' ? __('Top') : __('Recent') }}</p>
                            </x-square-button>
                        </x-slot:trigger>

                        <x-slot:content>
                            <button
                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary {{ $sort === 'top' ? 'text-tint' : '' }}"
                                wire:click="selectSort('top')"
                            >
                                {{ __('Top') }}
                            </button>

                            <button
                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary {{ $sort === 'recent' ? 'text-tint' : '' }}"
                                wire:click="selectSort('recent')"
                            >
                                {{ __('Recent') }}
                            </button>
                        </x-slot:content>
                    </x-dropdown>
                </div>
            </div>
        </section>

        <section class="border-b border-primary">
            <div class="flex pl-4 pr-4">
                <button
                    class="flex-1 pt-3 pb-3 text-sm font-semibold border-b-2 {{ $tab === 'quotes' ? 'border-tint text-tint' : 'border-transparent text-secondary' }}"
                    wire:click="selectTab('quotes')"
                >
                    {{ __('Quotes') }}
                </button>

                <button
                    class="flex-1 pt-3 pb-3 text-sm font-semibold border-b-2 {{ $tab === 'reshares' ? 'border-tint text-tint' : 'border-transparent text-secondary' }}"
                    wire:click="selectTab('reshares')"
                >
                    {{ __('Re-shares') }}
                </button>
            </div>
        </section>

        @if ($feedMessages->count())
            <section class="mt-4">
                @if ($tab === 'quotes')
                    <div class="flex flex-col">
                        @foreach ($feedMessages as $message)
                            <livewire:components.feed.message-lockup :feed-message="$message" wire:key="{{ uniqid($message->id, true) }}" />
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col gap-4 pl-4 pr-4">
                        @foreach ($feedMessages as $message)
                            @if ($message->user)
                                <x-lockups.user-lockup :user="$message->user" :is-row="false" wire:key="reshare-user-{{ uniqid($message->id, true) }}" />
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 pl-4 pr-4">
                    {{ $feedMessages->links() }}
                </div>
            </section>
        @else
            <section class="flex flex-col items-center justify-center mt-4 pl-4 pr-4 text-center" style="min-height: 50vh;">
                @if ($tab === 'quotes')
                    @svg('bubble_left_and_bubble_right_fill', 'fill-current w-32 mb-4')

                    <p class="font-bold mb-2">{{ __('No Quotes') }}</p>

                    <p class="text-sm text-secondary mb-4 max-w-md">
                        {{ __("Add your take when sharing someone else's post and it'll show up here.") }}
                    </p>

                    @auth
                        <a
                            class="px-6 py-2 rounded-full bg-tint text-white font-semibold hover:opacity-90"
                            href="{{ route('feed.details', $feedMessage) }}"
                            wire:navigate.hover
                        >
                            {{ __('Quote') }}
                        </a>
                    @endauth
                @else
                    @svg('square_and_arrow_up_on_square_fill', 'fill-current w-32 mb-4')

                    <p class="font-bold mb-2">{{ __('Amplify posts you like') }}</p>

                    <p class="text-sm text-secondary mb-4 max-w-md">
                        {{ __("Share someone else's post on your timeline by reposting it. When you do, it'll show up here.") }}
                    </p>

                    @auth
                        <button
                            class="px-6 py-2 rounded-full bg-tint text-white font-semibold hover:opacity-90"
                            wire:click="toggleSimpleReShare"
                        >
                            {{ $feedMessage->isReShared ? __('Undo Re-share') : __('Re-share') }}
                        </button>
                    @endauth
                @endif
            </section>
        @endif
    </div>
</main>
