@props(['songs' => [], 'animeSongs' => [], 'showEpisodes' => true, 'showAnime' => false, 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($songs as $song)
        <x-lockups.music-lockup :song="$song" :show-episodes="$showEpisodes" :show-anime="$showAnime" :is-row="$isRow"  />
    @endforeach

    @foreach($animeSongs as $animeSong)
        <x-lockups.music-lockup :song="$animeSong->song" :anime="$animeSong->anime" :type="$animeSong->type" :position="$animeSong->position" :episodes="$animeSong->episodes" :show-episodes="$showEpisodes" :show-anime="$showAnime" :is-row="$isRow" />
    @endforeach

    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
</div>
