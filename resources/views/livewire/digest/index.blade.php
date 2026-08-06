<main>
    <x-slot:title>
        {{ __('Your week') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Catch up on the episodes and releases from the shows and games on your list this week.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Your week') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Catch up on the episodes and releases from the shows and games on your list this week.') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('digest.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        digest
    </x-slot:appArgument>

    <div class="pb-6">
        <section class="pt-4">
            <div class="xl:safe-area-inset">
                <x-section-nav>
                    <x-slot:title>{{ __('Your week') }}</x-slot:title>
                    <x-slot:description>{{ $this->windowLabel }}</x-slot:description>
                </x-section-nav>
            </div>
        </section>

        <livewire:digest.section type="drops" :reference="$reference" wire:key="digest-drops" />
        <livewire:digest.section type="recommendations" :reference="$reference" wire:key="digest-recommendations" />
        <livewire:digest.section type="rescue" :reference="$reference" wire:key="digest-rescue" />
        <livewire:digest.section type="up-next" :reference="$reference" wire:key="digest-up-next" />
        <livewire:digest.section type="trending" :reference="$reference" wire:key="digest-trending" />
        <livewire:digest.section type="birthdays" :reference="$reference" wire:key="digest-birthdays" />
        <livewire:digest.section type="momentum" :reference="$reference" wire:key="digest-momentum" />
        <livewire:digest.section type="growth" :reference="$reference" wire:key="digest-growth" />
    </div>
</main>
