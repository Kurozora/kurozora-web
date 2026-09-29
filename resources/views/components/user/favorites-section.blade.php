<div data-section>
    <section class="relative pb-6 mb-8 z-10">
        <x-section-nav class="flex flex-nowrap justify-between mb-5 xl:safe-area-inset-scroll">
            <x-slot:title>
                {{ $title }}
            </x-slot:title>

            <x-slot:action>
                @hasrole('superAdmin')
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endhasrole

                <x-section-nav-link href="{{ $seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
            </x-slot:action>
        </x-section-nav>

        @switch($type)
            @case(\App\Models\Anime::class)
                <x-rows.small-lockup :animes="$favorites" />
                @break
            @case(\App\Models\Game::class)
                <x-rows.small-lockup :games="$favorites" />
                @break
            @case(\App\Models\Manga::class)
                <x-rows.small-lockup :mangas="$favorites" />
                @break
        @endswitch
    </section>
</div>
