@props(['anime', 'relation', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap">
        <picture class="relative shrink-0 w-32 h-48 mr-2 mr-2 rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if (!empty($relation))
                    <p class="text-xs leading-tight font-semibold text-black/60 line-clamp-2">{{ $relation->name }}</p>
                @endif

                <div class="flex justify-between">
                    <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>

{{--                    <p class="text-xs leading-tight text-black/60 whitespace-nowrap">{{ $anime->status->name }}</p>--}}
                </div>

                <div class="space-y-1">
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(',  ', ' and ') : $anime->tagline }}</p>
{{--                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tv_rating->name }}</p>--}}

{{--                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ trans_choice('[0,1] :x Episode|[2,*] :x Episodes', $anime->episode_count, ['x' => $anime->episode_count]) }}</p>--}}

{{--                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __($anime->first_aired?->year) }}</p>--}}
                </div>

                <div class="inline-flex my-auto">
                    <p class="text-sm font-bold text-orange-500">{{ number_format($anime->stats?->rating_average, 1) }}</p>

                    <livewire:anime.star-rating :rating="$anime->stats?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                </div>
            </div>

            <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
        </div>
    </div>
</div>
