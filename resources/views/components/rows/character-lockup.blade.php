@props(['characters' => [], 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($characters as $character)
        <x-lockups.character-lockup :character="$character" :is-row="$isRow" />
    @endforeach

    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
    <div class="w-32 flex-grow"></div>
</div>
