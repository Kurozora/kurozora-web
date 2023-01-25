@props(['animes' => [], 'relatedAnimes' => [], 'mangas' => [], 'relatedMangas' => [], 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

@if (!empty($animes) || !empty($relatedAnimes))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach($animes as $anime)
            <x-lockups.small-lockup :anime="$anime" :is-row="$isRow" />
        @endforeach

        @foreach($relatedAnimes as $anime)
            <x-lockups.small-lockup :anime="$anime->related" :relation="$anime->relation" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif(!empty($mangas) || !empty($relatedMangas))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach($mangas as $manga)
            <x-lockups.small-lockup :manga="$manga" :is-row="$isRow" />
        @endforeach

        @foreach($relatedMangas as $manga)
            <x-lockups.small-lockup :manga="$manga->related" :relation="$manga->relation" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@endif
