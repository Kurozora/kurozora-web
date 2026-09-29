<div data-section>
    <section class="pb-8">
        <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
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
                <x-rows.small-lockup :animes="$models" />
                @break
            @case(\App\Models\Character::class)
                <x-rows.character-lockup :characters="$models" />
                @break
            @case(\App\Models\Manga::class)
                <x-rows.small-lockup :mangas="$models" />
                @break
            @case(\App\Models\Game::class)
                <x-rows.small-lockup :games="$models" />
                @break
        @endswitch
    </section>
</div>
