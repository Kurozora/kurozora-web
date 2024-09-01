@props(['characters' => [], 'mangaCasts' => [], 'page' => 1, 'perPage' => 25, 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($characters as $index => $character)
        <x-lockups.character-lockup :character="$character" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :is-row="$isRow" />
    @endforeach

    @foreach($mangaCasts as $index => $mangaCast)
        <x-lockups.character-lockup :character="$mangaCast->character" :cast-role="$mangaCast->castRole->name" :rank="($page - 1) * $perPage + $index + 1" :is-ranked="$isRanked" :isRow="$isRow" />
    @endforeach

    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
    <div class="w-28 flex-grow"></div>
</div>
