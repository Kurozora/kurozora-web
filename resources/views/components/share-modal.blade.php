@props(['link', 'embedLink' => null, 'title', 'imageUrl' => null, 'type' => null])

<x-dialog-modal {{ $attributes }} maxWidth="md">
    <x-slot:title>
        {{ __('Share') }}
    </x-slot:title>

    <x-slot:content>
        <x-lockups.share-lockup :link="$link" :embed-link="$embedLink" :title="$title" :image="$imageUrl" :type="$type" />
    </x-slot:content>

    <x-slot:footer>
        <x-button wire:click="$toggle('{{ $attributes->get('model') }}')">{{ __('Close') }}</x-button>
    </x-slot:footer>
</x-dialog-modal>
