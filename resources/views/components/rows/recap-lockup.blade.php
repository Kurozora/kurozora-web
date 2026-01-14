@props(['recaps' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between select-none pl-4 pr-4 ' . $class]) }}>
    @foreach ($recaps as $index => $recap)
        <x-lockups.recap-lockup :recap="$recap" :is-row="$isRow"  />
    @endforeach

    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
</div>
