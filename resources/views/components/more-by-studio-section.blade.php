<div data-section>
    <section class="pb-8">
        <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
            <x-slot:title>
                {{ __('More By :x', ['x' => $studio->name]) }}
            </x-slot:title>

            <x-slot:action>
                @hasrole('superAdmin')
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endhasrole
                <x-section-nav-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-section-nav-link>
            </x-slot:action>
        </x-section-nav>

        @switch ($kind)
            @case (\App\Enums\UserLibraryKind::Anime)
                <x-rows.small-lockup :animes="$moreByStudio" />
                @break

            @case (\App\Enums\UserLibraryKind::Manga)
                <x-rows.small-lockup :mangas="$moreByStudio" />
                @break

            @default
                <x-rows.small-lockup :games="$moreByStudio" />
        @endswitch
    </section>
</div>
