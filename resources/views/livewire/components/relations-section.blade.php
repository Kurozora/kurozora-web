<div>
    @if ($this->relations->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                <x-slot:title>
                    {{ $this->sectionTitle }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            @switch ($relatedKind)
                @case (\App\Enums\UserLibraryKind::Anime)
                    <x-rows.small-lockup :related-animes="$this->relations" />
                    @break
                @case (\App\Enums\UserLibraryKind::Manga)
                    <x-rows.small-lockup :related-mangas="$this->relations" />
                    @break
                @default
                    <x-rows.small-lockup :related-games="$this->relations" />
            @endswitch
        </section>
    @endif
</div>
