@props(['animes' => [], 'relatedAnimes' => [], 'mangas' => [], 'relatedMangas' => [], 'games' => [], 'relatedGames' => [], 'page' => 1, 'perPage' => 25, 'trackingEnabled' => true, 'showsSchedule' => false, 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';
@endphp

@if (!empty($animes) || !empty($relatedAnimes))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach ($animes as $index => $anime)
            <x-lockups.small-lockup :anime="$anime" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach ($relatedAnimes as $index => $anime)
            <x-lockups.small-lockup :anime="$anime->related" :relation="$anime->relation" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif (!empty($games) || !empty($relatedGames))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach ($games as $index => $game)
            <x-lockups.small-lockup :game="$game" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach ($relatedGames as $index => $game)
            <x-lockups.small-lockup :game="$game->related" :relation="$game->relation" :rank="($page - 1) * $perPage + $index + 1"  :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@elseif (!empty($mangas) || !empty($relatedMangas))
    <div {{ $attributes->merge(['class' => 'flex gap-4 justify-between ' . $class]) }}>
        @foreach ($mangas as $index => $manga)
            <x-lockups.small-lockup :manga="$manga" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach

        @foreach ($relatedMangas as $index => $manga)
            <x-lockups.small-lockup :manga="$manga->related" :relation="$manga->relation" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
        <div class="w-64 md:w-80 flex-grow"></div>
        <div class="w-64 md:w-80 flex-grow"></div>
    </div>
@endif
