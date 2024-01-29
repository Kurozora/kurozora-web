@props(['recap', 'isRow' => true])

@php
    $class = $isRow ? 'shrink-0' : 'flex-grow';
@endphp

<div wire:key="{{ uniqid(more_entropy: true) }}" class="relative pb-2 w-64 snap-normal snap-center {{ $class }}">
    <div class="flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <img class="w-full h-full object-cover"
                 width="320" height="320"
                 src="{{ asset('images/static/placeholders/music_album.webp') }}"
            >

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </x-picture>

        <a class="absolute w-full h-full" href="{{ route('recap.index', ['year' => $recap->year]) }}"></a>
    </div>
</div>
