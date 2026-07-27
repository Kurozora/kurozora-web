<x-dialog-modal maxWidth="md" model="showSheet">
    <x-slot:title>
        {{ $sheetData['title'] }}
    </x-slot:title>

    <x-slot:content>
        <div class="flex flex-col gap-2 pt-4 pb-4 pl-4 pr-4">
            <p>{{ $sheetData['message'] }}</p>
            <p class="text-sm text-secondary">{{ $sheetData['tipJarEnabled'] ? __('Available with :x+ or Pro.', ['x' => config('app.name')]) : __('Available with :x+.', ['x' => config('app.name')]) }}</p>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <div class="inline-flex items-center gap-2">
            <x-button variant="secondary" wire:click="$toggle('showSheet')">{{ __('Not Now') }}</x-button>

            @if ($sheetData['tipJarEnabled'])
                <x-link-button href="{{ route('tip-jar') }}" wire:navigate>{{ __('Tip Jar') }}</x-link-button>
            @endif

            <x-link-button href="{{ route('kurozora-plus') }}" wire:navigate>{{ __('See :x+', ['x' => config('app.name')]) }}</x-link-button>
        </div>
    </x-slot:footer>
</x-dialog-modal>
