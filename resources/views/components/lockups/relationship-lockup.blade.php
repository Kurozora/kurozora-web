@props(['relationship'])

@php
    $relatedPerson = $relationship->relatedPerson;
    $media = $relatedPerson?->getFirstMedia(\App\Enums\MediaCollection::Profile);
    $imageUrl = $relatedPerson?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp');

    $startYear = $relationship->started_on?->year;
    $endYear = $relationship->ended_on?->year;
    $dateRange = match (true) {
        $startYear && $endYear => __(':startYear – :endYear', ['startYear' => $startYear, 'endYear' => $endYear]),
        (bool) $startYear => __(':startYear – present', ['startYear' => $startYear]),
        default => null,
    };
@endphp

<div {{ $attributes->merge(['class' => 'lockup-relationship']) }}>
    <a class="absolute inset-0 z-10" href="{{ route('people.details', $relatedPerson) }}" wire:navigate></a>

    <div class="flex items-center gap-3">
        <picture
            class="relative aspect-square w-24 shrink-0 rounded-full overflow-hidden"
            style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
        >
            <img class="w-full h-full object-cover lazyload" style="object-position: {{ $media?->objectPositionStyle() ?? 'center' }};" data-sizes="auto" data-src="{{ $imageUrl }}" alt="{{ $relatedPerson?->full_name }} Profile Image" title="{{ $relatedPerson?->full_name }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
        </picture>

        <div class="flex flex-col min-w-0">
            <p class="leading-tight line-clamp-2" title="{{ $relatedPerson?->full_name }}">{{ $relatedPerson?->full_name }}</p>

            <p class="text-sm text-secondary leading-tight">{{ $relationship->type->description }}</p>

            @if (!empty($dateRange))
                <p class="text-xs text-secondary leading-tight">{{ $dateRange }}</p>
            @endif
        </div>
    </div>
</div>
