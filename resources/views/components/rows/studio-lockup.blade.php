@props(['studios' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="studio" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($studios as $index => $studio)
        <x-lockups.studio-lockup :studio="$studio" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
