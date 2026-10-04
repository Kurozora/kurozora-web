@props(['relationships' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($relationships as $relationship)
        <x-lockups.relationship-lockup :relationship="$relationship" />
    @endforeach
</x-rows.container>
