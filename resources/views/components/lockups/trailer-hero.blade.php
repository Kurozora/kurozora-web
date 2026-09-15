@props(['video'])

@php
    $title = $video?->videoable;

    $bannerUrl = $title?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())
        ?? $title?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster())
        ?? asset('images/static/placeholders/anime_banner.webp');

    $posterUrl = $title?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster())
        ?? asset('images/static/placeholders/anime_poster.webp');

    $detailsUrl = $title instanceof \App\Models\Game
        ? route('games.details', $title)
        : route('anime.details', $title);

    $ratingAverage = $title?->mediaStat?->rating_average ?? 0;
@endphp

@if (!empty($title))
    <div
        class="pl-4 pr-4"
        data-trailer-hero
        data-code="{{ $video->code }}"
        data-poster="{{ $bannerUrl }}"
    >
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-start 2xl:gap-6">
            <div class="w-full 2xl:w-3/5 2xl:shrink-0">
                <div class="relative w-full aspect-video" data-trailer-hero-slot>
                    <div class="trailer-hero-stage" data-trailer-hero-stage wire:ignore.self>
                        <div class="relative bg-secondary w-full h-full overflow-hidden rounded-2xl" data-trailer-hero-frame wire:ignore>
                            <img class="w-full h-full object-cover" src="{{ $bannerUrl }}" alt="{{ $title->title }}" title="{{ $title->title }}">
                        </div>

                        <button
                            class="hidden absolute top-0 left-0 w-full h-full rounded-xl"
                            type="button"
                            data-trailer-hero-return
                            title="{{ __('Back to the trailer') }}"
                            aria-label="{{ __('Back to the trailer') }}"
                        ></button>

                        <button
                            class="hidden absolute top-1.5 right-1.5 items-center justify-center w-7 h-7 bg-black/60 text-white rounded-full"
                            type="button"
                            data-trailer-hero-close
                            title="{{ __('Dismiss') }}"
                            aria-label="{{ __('Dismiss') }}"
                        >
                            @svg('xmark', 'fill-current', ['width' => 11])
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 min-w-0 flex-grow">
                <div class="flex flex-nowrap items-start gap-3">
                    <picture class="relative shrink-0 bg-secondary w-[90px] aspect-[2/3] rounded-lg overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $posterUrl }}" alt="{{ $title->title }}" title="{{ $title->title }}">
                    </picture>

                    <div class="flex flex-col gap-1 justify-between min-w-0 flex-grow self-stretch">
                        <div class="flex flex-col gap-1 min-w-0">
                            <a href="{{ $detailsUrl }}" wire:navigate>
                                <h2 class="text-2xl font-bold leading-tight line-clamp-2">{{ $title->title }}</h2>
                            </a>

                            <p class="text-sm leading-tight text-secondary line-clamp-1">{{ $video->getMetaLine() }}</p>

                            @if ($ratingAverage > 0)
                                <div class="flex items-center gap-1.5">
                                    <p class="text-xs font-bold text-tint">{{ number_format($ratingAverage, 1) }}</p>

                                    <livewire:components.star-rating :rating="$ratingAverage" :star-size="'sm'" :disabled="true" wire:key="hero-rating-{{ $title->getMorphClass() }}-{{ $title->id }}" />
                                </div>
                            @endif
                        </div>

                        <div class="flex">
                            <livewire:components.library-button :model="$title" wire:key="hero-library-{{ $title->getMorphClass() }}-{{ $title->id }}" />
                        </div>
                    </div>
                </div>

                <p class="text-sm leading-normal text-secondary">{{ $title->synopsis }}</p>
            </div>
        </div>
    </div>
@endif
