<main>
    <x-slot name="title">
        {{ __('Episode :x', ['x' => $episode->number_total]) }} | {!! $episode->title !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Episode :x', ['x' => $episode->number_total]) }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $episode->synopsis }}" />
        <meta property="og:image" content="{{ $episode->banner_image_url ?? asset('images/static/placeholders/episode_banner.jpg') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->first_aired }}" />
        <meta property="video:series" content="{{ $episode->season->anime->title }}" />
    </x-slot>

    <x-slot name="appArgument">
        episodes/{{ $episode->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4">
        </section>

        <section class="mt-4">
        </section>
    </div>
</main>
