@props(['animes' => [], 'relatedAnimes' => [], 'mangas' => [], 'relatedMangas' => [], 'games' => [], 'relatedGames' => [], 'page' => 1, 'perPage' => 25, 'trackingEnabled' => true, 'showsSchedule' => false, 'isRanked' => false, 'isRow' => true, 'safeAreaInsetEnabled' => true, 'marksLibrary' => false, 'details' => []])

@if (!empty($animes) || !empty($relatedAnimes))
    <x-rows.container :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($animes as $index => $anime)
            <x-lockups.small-lockup :anime="$anime" :rank="($page - 1) * $perPage + $index + 1" :detail="$details[$anime->id] ?? null" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" :in-library="$marksLibrary && $anime->library->isNotEmpty()" />
        @endforeach

        @foreach ($relatedAnimes as $index => $anime)
            <x-lockups.small-lockup :anime="$anime->related" :relation="$anime->relation" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
    </x-rows.container>
@elseif (!empty($games) || !empty($relatedGames))
    <x-rows.container :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($games as $index => $game)
            <x-lockups.small-lockup :game="$game" :rank="($page - 1) * $perPage + $index + 1" :detail="$details[$game->id] ?? null" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" :in-library="$marksLibrary && $game->library->isNotEmpty()" />
        @endforeach

        @foreach ($relatedGames as $index => $game)
            <x-lockups.small-lockup :game="$game->related" :relation="$game->relation" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
    </x-rows.container>
@elseif (!empty($mangas) || !empty($relatedMangas))
    <x-rows.container :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($mangas as $index => $manga)
            <x-lockups.small-lockup :manga="$manga" :rank="($page - 1) * $perPage + $index + 1" :detail="$details[$manga->id] ?? null" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" :in-library="$marksLibrary && $manga->library->isNotEmpty()" />
        @endforeach

        @foreach ($relatedMangas as $index => $manga)
            <x-lockups.small-lockup :manga="$manga->related" :relation="$manga->relation" :rank="($page - 1) * $perPage + $index + 1" :tracking-enabled="$trackingEnabled" :shows-schedule="$showsSchedule" :is-ranked="$isRanked" :is-row="$isRow" />
        @endforeach
    </x-rows.container>
@endif
