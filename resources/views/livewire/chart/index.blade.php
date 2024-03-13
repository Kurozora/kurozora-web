<main>
    <x-slot:title>
        {{ __('Top Charts on Kurozora') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the top free anime, manga, games and music on Kurozora, like One Piece, Attack on Titan, Demon Slayer, My Hero Academia, Bleach and more!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Top Charts on Kurozora') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the top free anime, manga, games and music on Kurozora, like One Piece, Attack on Titan, Demon Slayer, My Hero Academia, Bleach and more!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('charts.index') }}">
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Top Charts') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-4">
            @foreach($chartKinds as $chartKind)
                <livewire:components.chart.section :chart-kind="$chartKind" />
            @endforeach
        </section>
    </div>
</main>
