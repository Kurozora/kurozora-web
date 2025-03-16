@props(['reviews' => [], 'isRow' => true])

@php
    $class = $isRow ? 'overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    @foreach ($reviews as $review)
        <x-lockups.review-lockup :review="$review" :is-row="$isRow" />
    @endforeach

    <div class="w-64 sm:w-96 flex-grow"></div>
    <div class="w-64 sm:w-96 flex-grow"></div>
</div>
