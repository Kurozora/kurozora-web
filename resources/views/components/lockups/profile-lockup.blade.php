@props(['name', 'media' => null, 'imageUrl', 'href', 'role' => null, 'rank', 'isRanked' => false, 'isRow' => true, 'isWide' => false])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
    $width = $isWide ? 'w-60' : 'w-28';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow ' . $width . ' ' . $class]) }}>
    <a class="absolute w-full h-full" href="{{ $href }}" wire:navigate></a>

    <div class="flex flex-col">
        <picture
            @class(['relative aspect-square rounded-full overflow-hidden'])
            style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
        >
            <img class="w-full h-full object-cover lazyload" style="object-position: {{ $media?->objectPositionStyle() ?? 'center' }};" data-sizes="auto" data-src="{{ $imageUrl }}" alt="{{ $name }} Profile Image" title="{{ $name }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ $href }}" wire:navigate></a>
    </div>

    <div class="flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div class="text-center">
                @if ($isRanked)
                    <p class="text-sm leading-tight font-semibold" title="{{ __('Ranked #:x', ['x' => $rank]) }}">{{ __('#:x', ['x' => $rank]) }}</p>
                @endif

                <p class="text-center leading-tight line-clamp-2" title="{{ $name }}">{{ $name }}</p>
            </div>

            @if (!empty($role))
                <p class="text-sm text-secondary text-center leading-tight line-clamp-2" title="{{ $role }}">{{ $role }}</p>
            @endif
        </div>
    </div>
</div>
