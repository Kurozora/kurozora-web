<x-dialog-modal maxWidth="md" id="alert">
    <x-slot:title>
        <span data-alert-title></span>
    </x-slot:title>

    <x-slot:content>
        <div class="pt-4 pb-4 pl-4 pr-4">
            <p data-alert-message></p>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <x-button x-on:click="show = false">{{ __('OK') }}</x-button>
    </x-slot:footer>
</x-dialog-modal>
