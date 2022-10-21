@props(['animes' => [], 'relatedAnimes' => [], 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($animes as $anime)
        <x-lockups.small-lockup :anime="$anime" :is-row="$isRow" />
    @endforeach

    @foreach($relatedAnimes as $anime)
        <x-lockups.small-lockup :anime="$anime->related" :relation="$anime->relation" :is-row="$isRow" />
    @endforeach
    <div class="w-64 md:w-80 flex-grow"></div>
    <div class="w-64 md:w-80 flex-grow"></div>
</div>
