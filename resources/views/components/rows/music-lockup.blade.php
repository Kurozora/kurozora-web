@props(['songs' => [], 'mediaSongs' => [], 'showEpisodes' => true, 'showModel' => false, 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($songs as $song)
        <x-lockups.music-lockup :song="$song" :show-episodes="$showEpisodes" :show-model="$showModel" :is-row="$isRow"  />
    @endforeach

    @foreach($mediaSongs as $mediaSong)
        <x-lockups.music-lockup :song="$mediaSong->song" :anime="$mediaSong->anime" :type="$mediaSong->type" :position="$mediaSong->position" :episodes="$mediaSong->episodes" :show-episodes="$showEpisodes" :show-model="$showModel" :is-row="$isRow" />
    @endforeach

    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
</div>
