@props(['episodes' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="episode" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($episodes as $index => $episode)
        <x-lockups.episode-lockup :episode="$episode" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
