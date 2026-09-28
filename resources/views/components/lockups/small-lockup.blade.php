@props(['anime', 'manga', 'game', 'relation', 'rank', 'eyebrow' => null, 'detail' => null, 'favoriteStatus' => null, 'trackingEnabled' => true, 'showsSchedule' => false, 'isRanked' => false, 'isRow' => true, 'inLibrary' => false])

@if (!empty($anime))
    <x-lockups.media-lockup
        :model="$anime"
        :kind="\App\Enums\UserLibraryKind::Anime"
        :href="route('anime.details', $anime)"
        :schedule-date="$showsSchedule ? $anime->broadcast_date : null"
        :schedule-duration="$anime->duration"
        :schedule-title="$showsSchedule ? __('Broadcasts at :x', ['x' => $anime->broadcast_date?->format('H:i T')]) : null"
        :relation="$relation ?? null"
        :rank="$rank ?? null"
        :eyebrow="$eyebrow"
        :detail="$detail"
        :favorite-status="$favoriteStatus"
        :tracking-enabled="$trackingEnabled"
        :shows-schedule="$showsSchedule"
        :is-ranked="$isRanked"
        :is-row="$isRow"
        :in-library="$inLibrary"
        {{ $attributes }}
    />
@elseif (!empty($game))
    <x-lockups.media-lockup
        :model="$game"
        :kind="\App\Enums\UserLibraryKind::Game"
        :href="route('games.details', $game)"
        :schedule-date="$game->publication_date"
        :schedule-title="__('Publishes at :x', ['x' => $game->publication_date?->format('H:i T')])"
        :clamp="1"
        :relation="$relation ?? null"
        :rank="$rank ?? null"
        :eyebrow="$eyebrow"
        :detail="$detail"
        :favorite-status="$favoriteStatus"
        :tracking-enabled="$trackingEnabled"
        :shows-schedule="$showsSchedule"
        :is-ranked="$isRanked"
        :is-row="$isRow"
        :in-library="$inLibrary"
        {{ $attributes }}
    />
@elseif (!empty($manga))
    <x-lockups.media-lockup
        :model="$manga"
        :kind="\App\Enums\UserLibraryKind::Manga"
        :href="route('manga.details', $manga)"
        :schedule-date="$manga->publication_date"
        :relation="$relation ?? null"
        :rank="$rank ?? null"
        :eyebrow="$eyebrow"
        :detail="$detail"
        :favorite-status="$favoriteStatus"
        :tracking-enabled="$trackingEnabled"
        :shows-schedule="$showsSchedule"
        :is-ranked="$isRanked"
        :is-row="$isRow"
        :in-library="$inLibrary"
        {{ $attributes }}
    />
@endif
