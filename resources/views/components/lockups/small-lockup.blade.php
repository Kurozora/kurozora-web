@props(['anime', 'manga', 'game', 'relation', 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    @if(!empty($anime))
        <div class="flex flex-nowrap gap-2">
            <picture class="relative shrink-0 w-32 h-48 rounded-lg overflow-hidden">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold text-black/60 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    <div class="flex justify-between">
                        <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 whitespace-nowrap">{{ $anime->mediaStatus->name }}</p>--}}
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(',  ', ' and ') : $anime->tagline }}</p>
                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tv_rating->name }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ trans_choice('[0,1] :x Episode|[2,*] :x Episodes', $anime->episode_count, ['x' => $anime->episode_count]) }}</p>--}}
{{--    --}}
{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __($anime->started_at?->year) }}</p>--}}
                    </div>

                    <div class="inline-flex my-auto">
                        <p class="text-sm font-bold text-orange-500">{{ number_format($anime->mediaStat?->rating_average ?? 0, 1) }}</p>

                        <livewire:anime.star-rating :rating="$anime->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                    </div>
                </div>

                <livewire:anime.library-button :anime="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
            </div>
        </div>
    @elseif(!empty($game))
        <div class="flex flex-nowrap gap-1">
            <picture class="relative shrink-0 w-32 h-32 rounded-3xl overflow-hidden">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $game->title }} Poster" title="{{ $game->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('games.details', $game) }}"></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold text-black/60 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    <div class="flex justify-between">
                        <p class="leading-tight line-clamp-1">{{ $game->title }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 whitespace-nowrap">{{ $game->mediaStatus->name }}</p>--}}
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs leading-tight text-black/60 line-clamp-1">{{ empty($game->tagline) ? $game->genres?->pluck('name')->join(',  ', ' and ') : $game->tagline }}</p>
                        <p class="text-xs leading-tight text-black/60 line-clamp-1">{{ $game->tv_rating->name }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ trans_choice('[0,1] :x Episode|[2,*] :x Episodes', $game->episode_count, ['x' => $game->episode_count]) }}</p>--}}
{{--    --}}
{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __($game->published_at?->year) }}</p>--}}
                    </div>

                    <div class="inline-flex my-auto">
                        <p class="text-sm font-bold text-orange-500">{{ number_format($game->mediaStat?->rating_average ?? 0, 1) }}</p>

                        <livewire:game.star-rating :rating="$game->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                    </div>
                </div>

                <livewire:game.library-button :game="$game" wire:key="{{ uniqid($game->id, true) }}" />
            </div>
        </div>
    @elseif(!empty($manga))
        <div class="flex flex-nowrap gap-1">
            <svg class="relative shrink-0 w-32 h-48 overflow-hidden" width="160" height="240" viewBox="0 0 160 240">
                <foreignObject height="240" width="160" mask="url(#svg-mask-book-cover)">
                    <img class="h-full w-full object-cover lazyload" data-sizes="auto" data-src="{{ $manga->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $manga->title }} Poster" title="{{ $manga->title }}" />
                </foreignObject>

                <g opacity="0.40">
                    <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                </g>
            </svg>

            <a class="absolute w-full h-full" href="{{ route('manga.details', $manga) }}"></a>

            <div class="flex flex-col w-full gap-2 justify-between">
                <div>
                    @if ($isRanked)
                        <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                    @endif

                    @if (!empty($relation))
                        <p class="text-xs leading-tight font-semibold text-black/60 line-clamp-2">{{ $relation->name }}</p>
                    @endif

                    <div class="flex justify-between">
                        <p class="leading-tight line-clamp-2">{{ $manga->title }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 whitespace-nowrap">{{ $manga->mediaStatus->name }}</p>--}}
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ empty($manga->tagline) ? $manga->genres?->pluck('name')->join(',  ', ' and ') : $manga->tagline }}</p>
                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $manga->tv_rating->name }}</p>

{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ trans_choice('[0,1] :x Episode|[2,*] :x Episodes', $manga->episode_count, ['x' => $manga->episode_count]) }}</p>--}}
{{--    --}}
{{--                        <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __($manga->started_at?->year) }}</p>--}}
                    </div>

                    <div class="inline-flex my-auto">
                        <p class="text-sm font-bold text-orange-500">{{ number_format($manga->mediaStat?->rating_average ?? 0, 1) }}</p>

                        <livewire:manga.star-rating :rating="$manga->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                    </div>
                </div>

                <livewire:manga.library-button :manga="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
            </div>
        </div>
    @endif
</div>
