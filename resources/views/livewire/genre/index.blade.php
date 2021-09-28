<main>
    <x-slot name="title">
        {{ __('Genres') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Genres') }} — {{ config('app.name') }}" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.png') }}" />
        <meta property="og:type" content="website" />
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
