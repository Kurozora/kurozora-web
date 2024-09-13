@props(['anime', 'manga', 'game', 'relation', 'rank', 'trackingEnabled' => true, 'showsSchedule' => false, 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    @if (!empty($anime))
        <div class="flex flex-nowrap gap-2">
            <picture
                class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden"
                style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}" wire:navigate></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    @if ($showsSchedule)
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">
                            {{ $anime->broadcast_date?->format('H:i T') }}
                            <span class="font-normal">({{ $anime->time_until_broadcast }})</span>
                        </p>
                    @endif

                    <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>

                    <div class="flex flex-col gap-1">
                        <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ empty($anime->tagline) ? ($anime->genres?->pluck('name')->join(', ', ' and ') ?? $anime->themes?->pluck('name')->join(', ', ' and ')) : $anime->tagline }}</p>
                        <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ $anime->tv_rating->name }}</p>
                    </div>

                    @if (!empty($anime->mediaStat?->rating_count) && $trackingEnabled)
                        <div class="inline-flex items-center gap-1 my-auto">
                            <p class="text-sm font-bold text-orange-500">{{ number_format($anime->mediaStat?->rating_average ?? 0, 1) }}</p>

                            <livewire:components.star-rating :rating="$anime->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                        </div>
                    @endif
                </div>

                @if ($trackingEnabled)
                    <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
               @endif
            </div>
        </div>
    @elseif (!empty($game))
        <div class="flex flex-nowrap gap-2">
            <picture
                class="relative shrink-0 w-28 h-28 rounded-3xl overflow-hidden"
                style="background-color: {{ $game->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $game->title }} Poster" title="{{ $game->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('games.details', $game) }}" wire:navigate></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    @if ($showsSchedule)
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">
                            {{ $game->publication_date?->format('H:i T') }}
                            <span class="font-normal">({{ $game->time_until_publication }})</span>
                        </p>
                    @endif

                    <p class="leading-tight line-clamp-1">{{ $game->title }}</p>

                    <div class="flex flex-col gap-1">
                        <p class="text-xs leading-tight opacity-75 line-clamp-1">{{ empty($game->tagline) ? ($game->genres?->pluck('name')->join(', ', ' and ') ?? $game->themes?->pluck('name')->join(', ', ' and ')) : $game->tagline }}</p>
                        <p class="text-xs leading-tight opacity-75 line-clamp-1">{{ $game->tv_rating->name }}</p>
                    </div>

                    @if (!empty($game->mediaStat?->rating_count) && $trackingEnabled)
                        <div class="inline-flex items-center gap-1 my-auto">
                            <p class="text-sm font-bold text-orange-500">{{ number_format($game->mediaStat?->rating_average ?? 0, 1) }}</p>

                            <livewire:components.star-rating :rating="$game->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                        </div>
                    @endif
                </div>

                 @if ($trackingEnabled)
                    <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />
                 @endif
            </div>
        </div>
    @elseif (!empty($manga))
        <div class="flex flex-nowrap gap-2">
            <svg class="relative shrink-0 w-28 h-40 overflow-hidden">
                <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                    <img
                        class="h-full w-full object-cover lazyload"
                        style="background-color: {{ $manga->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
                        data-sizes="auto"
                        data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}"
                        alt="{{ $manga->title }} Poster"
                        title="{{ $manga->title }}"
                    >
                </foreignObject>

                <g opacity="0.40">
                    <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                </g>
            </svg>

            <a class="absolute w-full h-full" href="{{ route('manga.details', $manga) }}" wire:navigate></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    @if ($showsSchedule)
                        <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2">
                            {{ $manga->publication_date?->format('H:i T') }}
                            <span class="font-normal">({{ $manga->time_until_publication }})</span>
                        </p>
                    @endif

                    <p class="leading-tight line-clamp-2">{{ $manga->title }}</p>

                    <div class="flex flex-col gap-1">
                        <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ empty($manga->tagline) ? ($manga->genres?->pluck('name')->join(', ', ' and ') ?? $manga->themes?->pluck('name')->join(', ', ' and ')) : $manga->tagline }}</p>
                        <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ $manga->tv_rating->name }}</p>
                    </div>

                    @if (!empty($manga->mediaStat?->rating_count) && $trackingEnabled)
                        <div class="inline-flex items-center gap-1 my-auto">
                            <p class="text-sm font-bold text-orange-500">{{ number_format($manga->mediaStat?->rating_average ?? 0, 1) }}</p>

                            <livewire:components.star-rating :rating="$manga->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                        </div>
                    @endif
                </div>

                @if ($trackingEnabled)
                    <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
                @endif
            </div>
        </div>
    @endif
</div>
