<x-base-layout>
    <x-slot:title>
        {{ __('Feed') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discuss anime, manga, games and more. Check out the forums on :x, the world’s most active online anime and manga community.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Feed') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discuss anime, manga, games and more. Check out the forums on :x, the world’s most active online anime and manga community.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('feed.index') }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/gif.js', 'resources/js/markdown.js', 'resources/js/watch.js', 'resources/js/feed.js'])
    </x-slot:scripts>

    <x-slot:appArgument>
        feed
    </x-slot:appArgument>

    <main>
        <div class="pb-6 xl:safe-area-inset">
            <section class="sticky top-0 pt-4 pb-4 backdrop-blur bg-blur z-10">
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Feed') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <div></div>
            </section>

            <section class="flex flex-row gap-2 mt-4 pl-4 pr-4">
                <x-profile-image-view class="w-12 h-12" :user="auth()->user()" />

                <div class="flex flex-col gap-2 w-full">
                    <livewire:components.feed-message-composer is-reply="false" />

{{--                    <x-textarea wire:model.live.debounce.500ms="message" :autoresize="true" />--}}

{{--                    @if ($linkPreview)--}}
{{--                        <div class="mt-4 border rounded p-3">--}}
{{--                            @if ($linkPreview->embed_html)--}}
{{--                                {!! $linkPreview->embed_html !!}--}}
{{--                            @else--}}
{{--                                <div>--}}
{{--                                    <p class="text-sm text-secondary">{{ $linkPreview->provider }}</p><br>--}}
{{--                                    <p class="text-primary">{{ $linkPreview->title }}</p><br>--}}
{{--                                    <p class="text-sm text-secondary line-clamp-2">{!! nl2br(e($linkPreview->description)) !!}</p><br>--}}
{{--                                    @if ($linkPreview->media_url)--}}
{{--                                        <img src="{{ $linkPreview->media_url }}" class="mt-2 rounded max-w-2xl" />--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    @endif--}}

{{--                    <div class="flex justify-end">--}}
{{--                        <x-tinted-pill-button--}}
{{--                            :color="'orange'"--}}
{{--                            title="{{ __('Post') }}"--}}
{{--                            wire:loading.attr="disabled"--}}
{{--                        >--}}
{{--                            {{ __('Post') }}--}}
{{--                        </x-tinted-pill-button>--}}
{{--                    </div>--}}
                </div>
            </section>

            <section class="mt-4 border-t border-primary">
                <x-feed.message-list />
            </section>
        </div>

        <x-feed.message-modals />
    </main>
</x-base-layout>
