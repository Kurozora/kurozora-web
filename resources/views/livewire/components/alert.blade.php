<x-dialog-modal maxWidth="md" model="showAlert">
    <x-slot:title>
        {{ $alertData['title'] }}
    </x-slot:title>

    <x-slot:content>
        <p>{{ $alertData['message'] }}</p>
    </x-slot:content>

    <x-slot:footer>
        <x-button wire:click="$toggle('showAlert')">{{ __('Ok') }}</x-button>
    </x-slot:footer>
</x-dialog-modal>
