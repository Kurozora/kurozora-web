@props(['characters' => [], 'mangaCasts' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="person" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($characters as $index => $character)
        <x-lockups.character-lockup :character="$character" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @foreach ($mangaCasts as $index => $mangaCast)
        <x-lockups.character-lockup :character="$mangaCast->character" :cast-role="$mangaCast->castRole->name" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
