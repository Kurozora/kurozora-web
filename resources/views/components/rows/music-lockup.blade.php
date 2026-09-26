@props(['songs' => [], 'mediaSongs' => [], 'showEpisodes' => true, 'showModel' => false, 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="music" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($songs as $index => $song)
        <x-lockups.music-lockup :song="$song" :show-episodes="$showEpisodes" :show-model="$showModel" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @foreach ($mediaSongs as $index => $mediaSong)
        <x-lockups.music-lockup :song="$mediaSong->song" :anime="$showModel ? $mediaSong->model : null" :type="$mediaSong->type" :position="$mediaSong->position" :episodes="$mediaSong->episodes" :show-episodes="$showEpisodes" :show-model="$showModel" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
