<main>
    <x-slot name="title">
        Anime | {!! $character->name !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="Anime | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $character->about ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $character->profile_image_url ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
    </x-slot>

    <x-slot name="appArgument">
        character/{{ $character->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4">
            @foreach($characterAnime as $anime)
                <x-lockups.small-lockup :anime="$anime" :isRow="false" wire:key="{{ $anime->id }}" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $characterAnime->links() }}
        </section>
    </div>
</main>
