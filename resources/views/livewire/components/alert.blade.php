<x-dialog-modal maxWidth="md" model="showAlert">
    <x-slot:title>
        {{ $alertData['title'] }}
    </x-slot:title>

    <x-slot:content>
        <div class="pt-4 pb-4 pl-4 pr-4">
            <p>{{ $alertData['message'] }}</p>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <x-button wire:click="$toggle('showAlert')">{{ __('Ok') }}</x-button>
    </x-slot:footer>
</x-dialog-modal>
