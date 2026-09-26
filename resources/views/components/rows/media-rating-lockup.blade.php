@props(['mediaRatings' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="media-rating" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($mediaRatings as $mediaRating)
        <x-lockups.media-rating-lockup :media-rating="$mediaRating" :is-row="$isRow" wire:key="{{ uniqid($mediaRating->id, true) }}" />
    @endforeach
</x-rows.container>
