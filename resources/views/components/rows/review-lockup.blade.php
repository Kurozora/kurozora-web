@props(['ratings' => [], 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
    @foreach($ratings as $rating)
        <x-lockups.review-lockup :rating="$rating" :is-row="$isRow" />
    @endforeach

    <div class="w-64 sm:w-96 flex-grow"></div>
    <div class="w-64 sm:w-96 flex-grow"></div>
</div>
