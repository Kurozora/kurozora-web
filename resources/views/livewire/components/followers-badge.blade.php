<x-profile-information-badge>
    <x-slot:title>{{ __('Followers') }}</x-slot>
    <x-slot:description>{{ $user->followers()->count() }}</x-slot>
</x-profile-information-badge>
