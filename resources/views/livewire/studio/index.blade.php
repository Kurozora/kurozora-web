<main>
    <x-slot name="title">
        {{ __('Studios') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Studios') }} — {{ config('app.name') }}" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <x-slot name="appArgument">
        studios
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($studios as $studio)
                <x-lockups.studio-lockup :studio="$studio" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $studios->links() }}
        </section>
    </div>
</main>
