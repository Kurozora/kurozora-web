@props(['video', 'isRow' => true, 'inLibrary' => false])

@php
    $title = $video->videoable;
    $detailsUrl = $title instanceof \App\Models\Game
        ? route('games.details', $title)
        : route('anime.details', $title);
@endphp

@if (!empty($title))
    <div {{ $attributes->merge(['class' => 'w-64 md:w-80 pb-2 ' . ($isRow ? 'snap-normal snap-center' : 'flex-grow')]) }} @if ($inLibrary) data-in-library @endif>
        <div class="relative bg-secondary aspect-video rounded-lg overflow-hidden">
            <iframe
                class="w-full h-full lazyload"
                type="text/html"
                loading="lazy"
                title="{{ __('Trailer for :x', ['x' => $title->title]) }}"
                allowfullscreen="allowfullscreen"
                allow="fullscreen;"
                data-size="auto"
                src="https://www.youtube-nocookie.com/embed/{{ $video->code }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1"
            >
            </iframe>
        </div>

        <div class="flex flex-nowrap items-start gap-2 mt-3">
            <a class="min-w-0 flex-grow" href="{{ $detailsUrl }}" wire:navigate>
                <p class="text-sm font-semibold leading-tight line-clamp-2" title="{{ $title->title }}">{{ $title->title }}</p>

                <p class="text-xs leading-tight text-secondary line-clamp-1 mt-1">{{ $video->getMetaLine() }}</p>
            </a>

            <div class="shrink-0">
                <livewire:components.library-button :model="$title" wire:key="{{ uniqid($video->id, true) }}" />
            </div>
        </div>
    </div>
@endif
