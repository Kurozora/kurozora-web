@props(['person', 'staffRole' => null, 'rank', 'isRanked' => false, 'isRow' => true, 'isWide' => false])

<x-lockups.profile-lockup
    :name="$person->full_name"
    :media="$person->getFirstMedia(\App\Enums\MediaCollection::Profile)"
    :image-url="$person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp')"
    :href="route('people.details', $person)"
    :role="$staffRole"
    :rank="$rank"
    :is-ranked="$isRanked"
    :is-row="$isRow"
    :is-wide="$isWide"
    {{ $attributes }}
/>
