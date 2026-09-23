@props(['character', 'castRole' => null, 'rank', 'isRanked' => false, 'isRow' => true, 'isWide' => false])

<x-lockups.profile-lockup
    :name="$character->name"
    :media="$character->getFirstMedia(\App\Enums\MediaCollection::Profile)"
    :image-url="$character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp')"
    :href="route('characters.details', $character)"
    :role="$castRole"
    :rank="$rank"
    :is-ranked="$isRanked"
    :is-row="$isRow"
    :is-wide="$isWide"
    {{ $attributes }}
/>
