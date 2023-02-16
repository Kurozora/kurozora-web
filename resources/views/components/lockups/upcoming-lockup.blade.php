@props(['anime' => null, 'manga' => null])

@if(!empty($anime))
    <div class="relative pb-2">
        <div class="flex flex-nowrap">
            <picture class="relative w-64 h-80 rounded-lg overflow-hidden sm:w-80 sm:h-[25rem] md:w-[22rem] md:h-[27rem]">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}" />

                <div
                    class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                    style="height: 20%; padding-top: 15%;"
                ></div>

                <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                    @if (empty($anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo())))
                        <p class="relative top-1/2 -translate-y-1/2 px-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $anime->title }}</p>
                    @else
                        <img class="relative top-1/2 -translate-y-1/2 px-8 lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo()) }}"
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
                        @if(auth()->user()->is_subscribed)
                            <livewire:anime.reminder-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
                        @else
                            <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
                        @endif
                    @else
                        <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
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
@else
    <div class="relative pb-2">
        <div class="flex flex-nowrap">
            <picture class="relative w-64 h-80 rounded-lg overflow-hidden sm:w-80 sm:h-[25rem] md:w-[22rem] md:h-[27rem]">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $manga->title }} Banner" title="{{ $manga->title }}" />

                <div
                    class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                    style="height: 20%; padding-top: 15%;"
                ></div>

                <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                    @if (empty($manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo())))
                        <p class="relative top-1/2 -translate-y-1/2 px-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $manga->title }}</p>
                    @else
                        <img class="relative top-1/2 -translate-y-1/2 px-8 lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo()) }}"
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
{{--                        @if(auth()->user()->is_subscribed)--}}
{{--                            <livewire:manga.reminder-button :manga="$manga" wire:key="{{ md5($manga->id) }}" />--}}
{{--                        @else--}}
                            <livewire:manga.library-button :manga="$manga" wire:key="{{ md5($manga->id) }}" />
{{--                        @endif--}}
                    @else
                        <livewire:manga.library-button :manga="$manga" wire:key="{{ md5($manga->id) }}" />
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
