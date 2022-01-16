<main>
    <x-slot name="title">
        {{ __('Genres') }}
    </x-slot>

    <x-slot name="description">
        {{ __('An extensive list of genres that include :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $genres->take(10)->pluck('name')->implode(', ')]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Genres') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of genres that include :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $genres->take(10)->pluck('name')->implode(', ')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('genres.index') }}">
    </x-slot>

    <x-slot name="appArgument">
        genres
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($genres as $genre)
                <x-lockups.genre-lockup :genre="$genre" />
            @endforeach
        </section>
    </div>
</main>
