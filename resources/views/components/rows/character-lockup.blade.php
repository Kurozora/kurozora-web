@props(['characters' => [], 'mangaCasts' => [], 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($characters as $character)
        <x-lockups.character-lockup :character="$character" :is-row="$isRow" />
    @endforeach

    @foreach($mangaCasts as $mangaCast)
        <x-lockups.character-lockup :character="$mangaCast->character" :cast-role="$mangaCast->castRole->name" :isRow="false" />
    @endforeach

    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
</div>
