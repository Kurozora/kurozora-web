@props(['videos' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'marksLibrary' => false])

@php
    $class = $isRow ? 'flex-nowrap snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

@if (!empty($videos))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
        @foreach ($videos as $video)
            <x-lockups.trailer-lockup :video="$video" :is-row="$isRow" :in-library="$marksLibrary && $video->videoable?->library?->isNotEmpty()" />
        @endforeach

        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@endif
