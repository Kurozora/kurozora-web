@props(['platforms' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach ($platforms as $index => $platform)
        <x-lockups.platform-lockup :platform="$platform" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    <div class="w-64 md:w-80 flex-grow"></div>
    <div class="w-64 md:w-80 flex-grow"></div>
    <div class="w-64 md:w-80 flex-grow"></div>
</div>
