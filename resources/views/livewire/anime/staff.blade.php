<main>
    <x-slot:title>
        {{ __('Staff') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all staff of :x only on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Staff') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all staff of :x on Kurozora, the largest, free online anime, manga & music database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
        <link rel="canonical" href="{{ route('anime.staff', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/staff
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <p class="text-2xl font-bold">{{ __(':x Staff', ['x' => $anime->title]) }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <x-rows.person-lockup :anime-staff="$this->staff" :is-row="false" />

        <section class="mt-4">
            {{ $this->staff->links() }}
        </section>
    </div>
</main>
