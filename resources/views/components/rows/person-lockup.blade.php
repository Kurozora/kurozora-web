@props(['people' => [], 'mediaStaff' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    @foreach ($people as $index => $person)
        <x-lockups.person-lockup :person="$person" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @foreach ($mediaStaff as $index => $staff)
        <x-lockups.person-lockup :person="$staff->person" :staff-role="$staff->staffRole->name" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @for ($spacerIndex = 0; $spacerIndex < 18; $spacerIndex++)
        <div class="w-28 flex-grow"></div>
    @endfor
</div>
