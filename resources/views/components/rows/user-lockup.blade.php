@props(['users' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true])

<x-rows.container lockup="user" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
    @foreach ($users as $user)
        <x-lockups.user-lockup :user="$user" :is-row="$isRow" />
    @endforeach
</x-rows.container>
