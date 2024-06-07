@props(['episode', 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap">
        <picture
            class="relative w-full aspect-video rounded-lg overflow-hidden"
            style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('episodes.details', $episode) }}" wire:navigate></a>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                <a href="{{ route('episodes.details', $episode) }}">
                    @if ($isRanked)
                        <p class="text-sm font-semibold">#{{ $rank }}</p>
                    @endif

                    <p class="text-xs line-clamp-2">{{ __('S:x · E:y', ['x' => $episode->season->number, 'y' => $episode->number_total]) }}</p>

                    <p class="line-clamp-2">{{ $episode->title }}</p>
                </a>

                <div class="mt-1">
                    <a class="text-xs text-orange-500 font-semibold line-clamp-2" href="{{ route('anime.details', $episode->anime) }}">{{ $episode->anime->title }}</a>

                    <p class="text-xs line-clamp-2" title="{{ $episode->started_at?->format('F d, Y H:i:s') }}">{{ __(':x views', ['x' => number_format($episode->view_count)]) . ' · ' . $episode->started_at?->toFormattedDateString() }}</p>
                </div>
            </div>

            <livewire:episode.watch-button :episode="$episode" wire:key="{{ uniqid($episode->id, true) }}" />
        </div>
    </div>
</div>
