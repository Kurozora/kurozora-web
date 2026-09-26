@props(['people' => [], 'mediaStaff' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="person" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($people as $index => $person)
        <x-lockups.person-lockup :person="$person" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @foreach ($mediaStaff as $index => $staff)
        <x-lockups.person-lockup :person="$staff->person" :staff-role="$staff->staffRole->name" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach
</x-rows.container>
