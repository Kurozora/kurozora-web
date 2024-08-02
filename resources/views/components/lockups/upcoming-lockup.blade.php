@props(['anime' => null, 'manga' => null, 'game' => null])

@if(!empty($anime))
    <div class="relative pb-2">
        <div class="flex flex-nowrap">
            <picture
                class="relative w-64 h-80 rounded-lg overflow-hidden sm:w-80 sm:h-[25rem] md:w-[22rem] md:h-[27rem]"
                style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}" />

                <div
                    class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                    style="height: 20%; padding-top: 15%;"
                ></div>

                <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                    @if (empty($anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo())))
                        <p class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $anime->title }}</p>
                    @else
                        <img class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo()) }}"
                        alt="{{ $anime->title }} Logo" title="{{ $anime->title }}" />
                    @endif
                </div>

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        </div>

        <a class="absolute bottom-0 w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

        <div class="absolute bottom-0 left-0 right-0 pt-3 pr-3 pl-3 pb-5">
            <div class="flex flex-col text-center mt-auto">
                <div class="h-10">
                    @auth
                        @if (auth()->user()->is_subscribed)
                            <livewire:anime.reminder-button :anime="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
                        @else
                            <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
                        @endif
                    @else
                        <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
                    @endauth
                </div>

                @if (empty($anime->started_at))
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Coming Soon') }}</p>
                @else
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Expected :x', ['x' => $anime->started_at->toFormattedDateString() ]) }}</p>
                @endif
            </div>
        </div>
    </div>
@elseif(!empty($game))
    <div class="relative pb-2">
        <div class="flex flex-nowrap">
            <picture
                class="relative w-64 h-80 rounded-lg overflow-hidden sm:w-80 sm:h-[25rem] md:w-[22rem] md:h-[27rem]"
                style="background-color: {{ $game->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $game->title }} Banner" title="{{ $game->title }}" />

                <div
                    class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                    style="height: 20%; padding-top: 15%;"
                ></div>

                <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                    @if (empty($game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo())))
                        <p class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $game->title }}</p>
                    @else
                        <img class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo()) }}"
                             alt="{{ $game->title }} Logo" title="{{ $game->title }}" />
                    @endif
                </div>

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        </div>

        <a class="absolute bottom-0 w-full h-full" href="{{ route('games.details', $game) }}"></a>

        <div class="absolute bottom-0 left-0 right-0 pt-3 pr-3 pl-3 pb-5">
            <div class="flex flex-col text-center mt-auto">
                <div class="h-10">
                    @auth
{{--                        @if (auth()->user()->is_subscribed)--}}
{{--                            <livewire:game.reminder-button :game="$game" wire:key="{{ uniqid($game->id, true) }}" />--}}
{{--                        @else--}}
                        <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />
{{--                        @endif--}}
                    @else
                        <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />
                    @endauth
                </div>

                @if (empty($game->started_at))
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Coming Soon') }}</p>
                @else
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Expected :x', ['x' => $game->started_at->toFormattedDateString() ]) }}</p>
                @endif
            </div>
        </div>
    </div>
@elseif(!empty($manga))
    <div class="relative pb-2">
        <div class="flex flex-nowrap">
            <picture
                class="relative w-64 h-80 rounded-lg overflow-hidden sm:w-80 sm:h-[25rem] md:w-[22rem] md:h-[27rem]"
                style="background-color: {{ $manga->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $manga->title }} Banner" title="{{ $manga->title }}" />

                <div
                    class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                    style="height: 20%; padding-top: 15%;"
                ></div>

                <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                    @if (empty($manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo())))
                        <p class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $manga->title }}</p>
                    @else
                        <img class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo()) }}"
                             alt="{{ $manga->title }} Logo" title="{{ $manga->title }}" />
                    @endif
                </div>

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        </div>

        <a class="absolute bottom-0 w-full h-full" href="{{ route('manga.details', $manga) }}"></a>

        <div class="absolute bottom-0 left-0 right-0 pt-3 pr-3 pl-3 pb-5">
            <div class="flex flex-col text-center mt-auto">
                <div class="h-10">
                    @auth
{{--                        @if (auth()->user()->is_subscribed)--}}
{{--                            <livewire:manga.reminder-button :manga="$manga" wire:key="{{ uniqid($manga->id, true) }}" />--}}
{{--                        @else--}}
                        <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
{{--                        @endif--}}
                    @else
                        <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
                    @endauth
                </div>

                @if (empty($manga->started_at))
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Coming Soon') }}</p>
                @else
                    <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Expected :x', ['x' => $manga->started_at->toFormattedDateString() ]) }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
