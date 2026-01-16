<main>
    <x-slot:title>
        {{ __('Projects') }}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x iOS, Android, Linux, Windows, and more!', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Projects') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __(':x iOS, Android, Linux, Windows, and more!', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('misc.projects') }}">
    </x-slot:meta>

    <div>
        {{-- Open-Source --}}
        <section class="relative pt-36 pb-10 overflow-hidden xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <div class="relative max-w-2xl mx-auto text-center">
                    <div class="flex flex-col items-center">
                        <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="{{ config('app.name') }}">

                        <h1 class="text-4xl font-bold leading-tight tracking-tight">{{ __('Open-Source at :x', ['x' => config('app.name')]) }}</h1>
                    </div>

                    <div class="mt-10">
                        <p class="text-lg font-light md:text-2xl">{{ __('Open-source software serves as the core of :x’s platforms and developer tools, fostering collaboration with developers worldwide to generate and distribute open-source code.', ['x' => config('app.name')]) }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Projects --}}
        <section class="pt-36 pb-10 bg-secondary xl:safe-area-inset">
            <div class="max-w-7xl mx-auto pt-4 pb-6 pl-4 pr-4">
                <div class="flex flex-wrap justify-between gap-4">
                    <div class="flex flex-col w-full gap-2 md:w-3/4">
                        <h2 class="text-4xl font-semibold">{{ __('Open-source projects') }}</h2>

                        <p class="text-lg font-light md:text-2xl">{{ __('Every :x product and service is crafted with and embraces open-source. We manage the following projects and encourages your contribution.', ['x' => config('app.name')]) }}</p>
                    </div>

                    <div>
                        <x-link-button class="whitespace-nowrap" href="{{ config('social.github.url') }}" target="_blank">{{ __('View on GitHub') }}</x-link-button>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between gap-4 mt-10">
                    @foreach ($projects as $project)
                        <div class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80">
                            <x-picture>
                                <img
                                    class="w-full h-full object-cover lazyload"
                                    title="{{ $project->name }}"
                                    alt="{{ $project->name }} Social Preview"
                                    data-src="{{ $project->imageURL ?? asset('images/static/placeholders/anime_banner.webp') }}"
                                    width="1280"
                                    height="640"
                                >
                            </x-picture>

                            <div class="flex flex-col justify-between pt-4 pr-2 pb-4 pl-2">
                                <div class="flex flex-col gap-2 mt-1">
                                    <p class="text-lg leading-tight line-clamp-2" title="{{ $project->name }}">{{ $project->name }}</p>

                                    <p class="text-sm text-secondary leading-tight line-clamp-2" title="{{ $project->stack }}">{{ $project->stack }}</p>

                                    <p class="text-sm leading-tight">{{ $project->description }}</p>
                                </div>
                            </div>

                            <a class="absolute bottom-0 w-full h-full no-external-icon" href="{{ config('social.github.url') }}/{{ $project->repo }}" target="_blank"></a>
                        </div>
                    @endforeach

                    <div class="relative flex-grow w-64 md:w-80"></div>
                    <div class="relative flex-grow w-64 md:w-80"></div>
                </div>
            </div>
        </section>
    </div>
</main>
