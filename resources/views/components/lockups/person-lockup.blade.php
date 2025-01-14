@props(['person', 'staffRole' => null, 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-28 ' . $class]) }}>
    <a class="absolute w-full h-full" href="{{ route('people.details', $person) }}"></a>

    <div class="flex flex-col">
        <picture
            class="relative aspect-square rounded-full shadow-md overflow-hidden"
            style="background-color: {{ $person->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $person->full_name }} Profile Image" title="{{ $person->full_name }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('people.details', $person) }}" wire:navigate></a>
    </div>

    <div class="flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div class="text-center">
                @if ($isRanked)
                    <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
                @endif

                <p class="text-center leading-tight line-clamp-2">{{ $person->full_name }}</p>
            </div>

            @if (!empty($staffRole))
                <p class="text-sm text-secondary text-center leading-tight line-clamp-2">{{ $staffRole }}</p>
            @endif
        </div>
    </div>
</div>
