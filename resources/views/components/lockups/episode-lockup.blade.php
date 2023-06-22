@props(['episode', 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap">
        <picture class="relative w-full aspect-video rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('episodes.details', $episode) }}"></a>

    <div class="relative mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if ($isRanked)
                    <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                @endif

                <p class="text-xs leading-tight line-clamp-2">{{ __('S:x · E:y', ['x' => $episode->season->number, 'y' => $episode->number_total]) }}</p>

                <p class="leading-tight line-clamp-2">{{ $episode->title }}</p>

                <div class="flex text-xs leading-tight font-semibold">
                    <a class="text-xs text-orange-500 leading-tight line-clamp-2" href="{{ route('anime.details', $episode->season->anime) }}">{{ $episode->season->anime->title }}</a>
                    &nbsp;
                    <p class="line-clamp-2"> · {{ __(':x views', ['x' => number_format($episode->view_count)]) . ' · ' . $episode->started_at?->toFormattedDateString() }}</p>
                </div>
            </div>

            <livewire:episode.watch-button :episode="$episode" wire:key="{{ uniqid($episode->id, true) }}" />
        </div>
    </div>
</div>
