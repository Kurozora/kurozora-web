@props(['season', 'isRow' => true])

@php
    $width = $isRow ? 'w-64 md:w-80' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 ' . $width]) }}>
    <div class="flex flex-no-wrap">
        <picture class="relative flex-shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden md:w-32 md:h-48">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('seasons.episodes', $season) }}"></a>

        <div class="flex flex-col gap-1 justify-between">
            <div>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __('Season') . ' ' . $season->number }}</p>
                <p class="leading-tight line-clamp-2">{{ $season->title }}</p>
            </div>

            <div class="flex flex-wrap gap-1.5 justify-between">
                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Premiere') }}</p>
                    <p class="text-sm">{{ $season->first_aired?->toFormattedDateString() }}</p>
                </div>
                <hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Episodes') }}</p>
                    <p class="text-sm">{{ $season->episodes()->count() }}</p>
                </div>
                <hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Score') }}</p>
                    <p class="text-sm">0.00</p>
                </div>
            </div>
        </div>
    </div>
</div>
