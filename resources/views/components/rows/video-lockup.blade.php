@props(['animes' => [], 'games' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'marksLibrary' => false])

@php
    $class = $isRow ? 'flex-nowrap snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

@if (!empty($animes))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
        @foreach ($animes as $anime)
            <x-lockups.video-lockup :anime="$anime" :is-row="$isRow" :in-library="$marksLibrary && $anime->library->isNotEmpty()" />
        @endforeach

        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif (!empty($games))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
        @foreach ($games as $game)
            <x-lockups.video-lockup :game="$game" :is-row="$isRow" :in-library="$marksLibrary && $game->library->isNotEmpty()" />
        @endforeach

        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@endif
