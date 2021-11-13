<main>
    <x-slot name="title">
       Anime | {!! $person->full_name !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="Anime | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $person->about }}" />
        <meta property="og:image" content="{{ $person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
    </x-slot>

    <x-slot name="appArgument">
        person/{{ $person->id }}/shows
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4">
            @foreach($personAnime as $anime)
                <x-lockups.small-lockup :anime="$anime" :isRow="false" wire:key="{{ $anime->id }}" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $personAnime->links() }}
        </section>
    </div>
</main>
