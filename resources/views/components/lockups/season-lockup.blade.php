@props(['season', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap gap-2">
        <picture
            class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden"
            style="background-color: {{ $season->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $season->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('seasons.episodes', $season) }}" wire:navigate></a>

        <div class="flex flex-col w-full justify-between">
            <div>
                <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ __('Season') . ' ' . $season->number }}</p>
                <p class="leading-tight line-clamp-2">{{ $season->title }}</p>
            </div>

            <div class="flex flex-wrap gap-1.5 justify-between">
                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm opacity-75">{{ __('Premiere') }}</p>
                    <p class="text-sm">{{ $season->started_at?->toFormattedDateString() }}</p>
                </div>

                <x-hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm opacity-75">{{ __('Episodes') }}</p>
                    <p class="text-sm">{{ $season->episodes_count }}</p>
                </div>

                <x-hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm opacity-75">{{ __('Score') }}</p>
                    <p class="text-sm">{{ number_shorten($season->rating_average ?? 0, 1) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
