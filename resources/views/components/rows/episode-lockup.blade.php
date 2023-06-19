@props(['episodes' => [], 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($episodes as $episode)
        <x-lockups.episode-lockup :episode="$episode" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    <div class="w-64 md:w-80 flex-grow"></div>
    <div class="w-64 md:w-80 flex-grow"></div>
    <div class="w-64 md:w-80 flex-grow"></div>
</div>
