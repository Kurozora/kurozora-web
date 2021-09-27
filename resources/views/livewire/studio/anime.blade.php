<main>
    <x-slot name="title">
        {!! $studio->name !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $studio->synopsis }}" />
        <meta property="og:image" content="{{ $studio->profile_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
    </x-slot>

    <x-slot name="appArgument">
        studio/{{ $studio->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4">
            @foreach($studioAnime as $anime)
                <x-lockups.small-lockup :anime="$anime" wire:key="{{ $anime->id }}" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $studioAnime->links() }}
        </section>
    </div>
</main>
