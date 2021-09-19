<main>
    <x-slot name="title">
        {{ __('Explore') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ config('app.name') }}" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.png') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <x-slot name="appArgument">
        explore
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 pb-6">
        @foreach($explorePageCategories as $key => $explorePageCategory)
            @switch($explorePageCategory->type)
            @case('most-popular-shows')
                <section class="flex pb-10 overflow-x-scroll no-scrollbar">
                    <div class="flex flex-nowrap space-x-4">
                        @foreach(App\Models\Anime::mostPopular()->get() as $anime)
                            <x-lockups.banner-lockup :anime="$anime" />
                        @endforeach
                    </div>
                </section>
                @break
            @case('shows')
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                        <x-slot name="title">
                            {{ $explorePageCategory->title }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="#">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    @switch($explorePageCategory->size)
                        @case('large')
                            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                                <div class="flex flex-nowrap space-x-4">
                                    @foreach($explorePageCategory->animes as $anime)
                                        <x-lockups.large-lockup :anime="$anime" />
                                    @endforeach
                                </div>
                            </div>
                            @break
                        @case('small')
                            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                                <div class="flex flex-nowrap space-x-4">
                                    @foreach($explorePageCategory->animes as $anime)
                                        <x-lockups.small-lockup :anime="$anime" />
                                    @endforeach
                                </div>
                            </div>
                            @break
                        @case('video')
                            <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                                <div class="flex flex-nowrap space-x-4">
                                    @foreach($explorePageCategory->animes as $anime)
                                        <x-lockups.video-lockup :anime="$anime" />
                                    @endforeach
                                </div>
                            </div>
                            @break
                        @default
                        {{ 'Unhandled size: ' . $explorePageCategory->size }}
                    @endswitch
                </section>
                @break
            @case('genres')
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                        <x-slot name="title">
                            {{ __('Top Genres') }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="{{ url('/genres') }}">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap space-x-4">
                            @foreach($explorePageCategory->genres as $genre)
                                <x-lockups.medium-lockup
                                    :href="url('/?genre=' . $genre->id)"
                                    :title="$genre->name"
                                    :backgroundColor="$genre->color"
                                    :backgroundImage="$genre->symbol ?? asset('images/static/icon/logo.png')"
                                />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
            @default
                {{ 'Unhandled type: ' . $explorePageCategory->type }}
            @endswitch
        @endforeach
    </div>
</main>
