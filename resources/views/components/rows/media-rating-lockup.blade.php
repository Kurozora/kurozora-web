@props(['mediaRatings' => [], 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    @foreach ($mediaRatings as $mediaRating)
        <x-lockups.media-rating-lockup :media-rating="$mediaRating" :is-row="$isRow" wire:key="{{ uniqid($mediaRating->id, true) }}" />
    @endforeach

    <div class="w-64 sm:w-96 flex-grow"></div>
    <div class="w-64 sm:w-96 flex-grow"></div>
</div>
