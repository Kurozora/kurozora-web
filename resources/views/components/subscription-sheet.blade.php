<x-dialog-modal maxWidth="md" id="subscription-sheet">
    <x-slot:title>
        <span data-subscription-title></span>
    </x-slot:title>

    <x-slot:content>
        <div
            class="flex flex-col gap-2 pt-4 pb-4 pl-4 pr-4"
            data-subscription-availability-with-tip-jar="{{ __('Available with :x+ or Pro.', ['x' => config('app.name')]) }}"
            data-subscription-availability-without-tip-jar="{{ __('Available with :x+.', ['x' => config('app.name')]) }}"
        >
            <p data-subscription-message></p>
            <p class="text-sm text-secondary" data-subscription-availability></p>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <div class="inline-flex items-center gap-2">
            <x-button variant="secondary" x-on:click="show = false">{{ __('Not Now') }}</x-button>

            <span class="hidden" data-subscription-tip-jar>
                <x-link-button href="{{ route('tip-jar') }}" wire:navigate>{{ __('Tip Jar') }}</x-link-button>
            </span>

            <x-link-button href="{{ route('kurozora-plus') }}" wire:navigate>{{ __('See :x+', ['x' => config('app.name')]) }}</x-link-button>
        </div>
    </x-slot:footer>
</x-dialog-modal>
