@props(['recaps' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="recap" class="select-none" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($recaps as $index => $recap)
        <x-lockups.recap-lockup :recap="$recap" :is-row="$isRow" />
    @endforeach
</x-rows.container>
