@props(['people' => [], 'animeStaff' => [], 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($people as $person)
        <x-lockups.person-lockup :person="$person" :is-row="$isRow" />
    @endforeach

    @foreach($animeStaff as $staff)
        <x-lockups.person-lockup :person="$staff->person" :staff-role="$staff->staff_role->name" :is-row="$isRow" />
    @endforeach

    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
</div>
