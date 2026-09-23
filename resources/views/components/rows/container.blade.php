@props(['lockup' => 'small', 'isRow' => true, 'safeAreaInsetEnabled' => true])

@php
    $spacerCount = match ($lockup) {
        'person' => 18,
        'person-wide' => 9,
        'music', 'medium' => 8,
        default => 6,
    };

    $spacerWidth = match ($lockup) {
        'person' => 'w-28',
        'person-wide' => 'w-60',
        'recap' => 'w-[98%]',
        'medium' => 'w-[98%] max-w-[16rem]',
        'music' => 'w-[98%] sm:w-64',
        'review', 'cast' => 'w-[98%] sm:w-96',
        'achievement' => 'w-[98%] sm:w-72',
        default => 'w-[98%] sm:w-80',
    };

    $class = $isRow ? 'snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    {{ $slot }}

    @for ($spacerIndex = 0; $spacerIndex < $spacerCount; $spacerIndex++)
        <div class="{{ $spacerWidth }} flex-grow"></div>
    @endfor
</div>
