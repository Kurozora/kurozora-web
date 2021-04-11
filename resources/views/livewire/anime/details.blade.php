<div class="flex flex-col w-full h-full items-center justify-center">
    <x-slot name="open-graph">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <div class="anime-poster" style="background-image: url('{{ $anime->poster()->url ?? asset('images/static/placeholders/anime_poster.jpg') }}')"></div>

    <h1 class="text-white font-bold mt-6 mb-2">{{ $anime->title }}</h1>

    @if($anime->episode_count)
        <h2 class="text-white">{{ $anime->episode_count }} {{ __('episodes') }}</h2>
    @endif

    <x-link-button href="{{ ios_app_url('anime/' . $anime->id) }}" class="rounded-full">
        {{ __('Open in Kurozora App') }}
    </x-link-button>
</div>
