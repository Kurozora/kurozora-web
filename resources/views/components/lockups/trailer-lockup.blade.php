@props(['video', 'isRow' => true, 'inLibrary' => false])

@php
    $title = $video->videoable;

    $detailsUrl = $title instanceof \App\Models\Game
        ? route('games.details', $title)
        : route('anime.details', $title);

    $bannerUrl = $title?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())
        ?? $title?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster())
        ?? asset('images/static/placeholders/anime_banner.webp');
@endphp

@if (!empty($title))
    <div
        {{ $attributes->merge(['class' => 'w-64 md:w-80 pb-2 ' . ($isRow ? 'snap-normal snap-center' : 'flex-grow')]) }}
        data-trailer-lockup
        data-code="{{ $video->code }}"
        data-poster="{{ $bannerUrl }}"
        @if ($inLibrary) data-in-library @endif
    >
        <button
            class="relative block bg-secondary w-full aspect-video rounded-xl overflow-hidden group"
            type="button"
            wire:click="feature({{ $video->id }})"
            data-trailer-play
            title="{{ __('Trailer for :x', ['x' => $title->title]) }}"
            aria-label="{{ __('Trailer for :x', ['x' => $title->title]) }}"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $bannerUrl }}" alt="{{ $title->title }}" title="{{ $title->title }}">

            <span class="absolute top-1/2 left-1/2 flex items-center justify-center w-12 h-12 -translate-x-1/2 -translate-y-1/2 bg-blur backdrop-blur rounded-full transition group-hover:scale-110">
                <span data-trailer-glyph-play>
                    @svg('play_fill', 'fill-current', ['width' => 20])
                </span>

                <span data-trailer-glyph-pause>
                    @svg('pause_fill', 'fill-current', ['width' => 20])
                </span>
            </span>
        </button>

        <div class="flex flex-nowrap items-start gap-2 mt-3">
            <a class="min-w-0 flex-grow" href="{{ $detailsUrl }}" wire:navigate>
                <p class="text-sm font-semibold leading-tight line-clamp-2" title="{{ $title->title }}">{{ $title->title }}</p>

                <p class="text-xs leading-tight text-secondary line-clamp-1 mt-1">{{ $video->getMetaLine() }}</p>
            </a>

            <div class="shrink-0">
                <livewire:components.library-button :model="$title" wire:key="trailer-library-{{ $video->id }}" />
            </div>
        </div>
    </div>
@endif
