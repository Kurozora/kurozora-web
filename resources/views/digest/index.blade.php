<x-base-layout>
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

    <main>
        <div class="pb-6">
            <section class="pt-4">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Your week') }}</x-slot:title>
                        <x-slot:description>{{ $windowLabel }}</x-slot:description>
                    </x-section-nav>
                </div>
            </section>

            <x-digest.section type="drops" :reference="$reference" />
            <x-digest.section type="recommendations" :reference="$reference" />
            <x-digest.section type="rescue" :reference="$reference" />
            <x-digest.section type="up-next" :reference="$reference" />
            <x-digest.section type="trending" :reference="$reference" />
            <x-digest.section type="birthdays" :reference="$reference" />
            <x-digest.section type="momentum" :reference="$reference" />
            <x-digest.section type="growth" :reference="$reference" />
        </div>
    </main>
</x-base-layout>
