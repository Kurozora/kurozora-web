<main>
    <x-slot name="title">
        {{ $anime->title }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}
    </x-slot>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] lg:grid-rows-none lg:grid-cols-3 h-full mb-4 lg:mb-0">
        <div class="relative">
            <picture>
                <img class="lg:h-full lg:object-cover" src="{{ asset('images/static/star_bg_lg.jpg') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
            </picture>
        </div>
    </div>

{{--    <div class="bg-center bg-cover bg-no-repeat border-2 border-gray-100 border-opacity-25 rounded-lg shadow-lg mt-3 mb-0 mx-auto w-[180px] h-[268px]" style="background-size: 180px 268px; background-image: url('{{ $anime->poster()->url ?? asset('images/static/placeholders/anime_poster.jpg') }}')"></div>--}}

{{--    <h1 class="font-bold mt-6 mb-2">{{ $anime->title }}</h1>--}}

{{--    @if ($anime->episode_count)--}}
{{--        <h2>{{ $anime->episode_count }} {{ __('episodes') }}</h2>--}}
{{--    @endif--}}

{{--    <x-link-button href="{{ ios_app_url('anime/' . $anime->id) }}" class="rounded-full">--}}
{{--        {{ __('Open in Kurozora App') }}--}}
{{--    </x-link-button>--}}
</main>
