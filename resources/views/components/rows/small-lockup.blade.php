@props(['animes' => [], 'relatedAnimes' => [], 'mangas' => [], 'relatedMangas' => [], 'games' => [], 'relatedGames' => [], 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

@if (!empty($animes) || !empty($relatedAnimes))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach($animes as $anime)
            <x-lockups.small-lockup :anime="$anime" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach($relatedAnimes as $anime)
            <x-lockups.small-lockup :anime="$anime->related" :relation="$anime->relation" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif(!empty($games) || !empty($relatedGames))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach($games as $game)
            <x-lockups.small-lockup :game="$game" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach($relatedGames as $game)
            <x-lockups.small-lockup :game="$game->related" :relation="$game->relation" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif(!empty($mangas) || !empty($relatedMangas))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach($mangas as $manga)
            <x-lockups.small-lockup :manga="$manga" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach($relatedMangas as $manga)
            <x-lockups.small-lockup :manga="$manga->related" :relation="$manga->relation" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@endif
