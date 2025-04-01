<main>
    <x-slot:title>
        {{ __('Genres') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of genres that include :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $genres->take(10)->pluck('name')->implode(', '), 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Genres') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of genres that include :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $genres->take(10)->pluck('name')->implode(', '), 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('genres.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        genres
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="grid gap-4 pl-4 pr-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($genres as $genre)
                <x-lockups.genre-lockup :genre="$genre" />
            @endforeach
        </section>
    </div>
</main>
