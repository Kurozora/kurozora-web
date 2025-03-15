@props(['recaps' => [], 'isRow' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between select-none pl-4 pr-4 ' . $class]) }}>
    @foreach ($recaps as $index => $recap)
        <x-lockups.recap-lockup :recap="$recap" :is-row="$isRow"  />
    @endforeach

    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
</div>
