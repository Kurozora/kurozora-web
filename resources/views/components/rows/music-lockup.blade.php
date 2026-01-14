@props(['songs' => [], 'mediaSongs' => [], 'showEpisodes' => true, 'showModel' => false, 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    @foreach ($songs as $index => $song)
        <x-lockups.music-lockup :song="$song" :show-episodes="$showEpisodes" :show-model="$showModel" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow"  />
    @endforeach

    @foreach ($mediaSongs as $index => $mediaSong)
        <x-lockups.music-lockup :song="$mediaSong->song" :anime="$showModel ? $mediaSong->model : null" :type="$mediaSong->type" :position="$mediaSong->position" :episodes="$mediaSong->episodes" :show-episodes="$showEpisodes" :show-model="$showModel" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
    <div class="w-64 flex-grow"></div>
</div>
