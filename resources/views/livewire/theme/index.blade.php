<main>
    <x-slot name="title">
        {{ __('Themes') }}
    </x-slot>

    <x-slot name="description">
        {{ __('An extensive list of themes that include :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $themes->take(10)->pluck('name')->implode(', ')]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Themes') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of themes that include :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $themes->take(10)->pluck('name')->implode(', ')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('themes.index') }}">
    </x-slot>

    <x-slot name="appArgument">
        themes
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($themes as $theme)
                <x-lockups.theme-lockup :theme="$theme" />
            @endforeach
        </section>
    </div>
</main>
