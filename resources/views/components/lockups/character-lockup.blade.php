@props(['character', 'castRole' => null, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-32 ' . $class]) }}>
    <div class="flex flex-col">
        <picture class="relative aspect-square rounded-full shadow-md overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $character->name }} Profile Image" title="{{ $character->name }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('characters.details', $character) }}"></a>
    </div>

    <div class="flex flex-col mt-2 gap-2">
        <p class="text-center leading-tight line-clamp-2">{{ $character->name }}</p>

        @if (!empty($castRole))
            <p class="text-md text-gray-500 text-center leading-tight line-clamp-2">{{ $castRole }}</p>
        @endif
    </div>
</div>
