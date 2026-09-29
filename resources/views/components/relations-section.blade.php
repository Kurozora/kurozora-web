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

        @switch ($relatedKind)
            @case (\App\Enums\UserLibraryKind::Anime)
                <x-rows.small-lockup :related-animes="$relations" />
                @break
            @case (\App\Enums\UserLibraryKind::Manga)
                <x-rows.small-lockup :related-mangas="$relations" />
                @break
            @default
                <x-rows.small-lockup :related-games="$relations" />
        @endswitch
    </section>
</div>
