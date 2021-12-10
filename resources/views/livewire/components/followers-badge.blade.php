<x-profile-information-badge>
    <x-slot name="title">{{ __('Followers') }}</x-slot>
    <x-slot name="description">{{ $user->followers()->count() }}</x-slot>
</x-profile-information-badge>
