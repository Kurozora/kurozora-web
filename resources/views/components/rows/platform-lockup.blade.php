@props(['platforms' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="platform" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($platforms as $index => $platform)
        <x-lockups.platform-lockup :platform="$platform" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
