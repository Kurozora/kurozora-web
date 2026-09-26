<div>
    @if ($this->cast->count())
        <section class="pb-8">
            @switch ($kind)
                @case (\App\Enums\UserLibraryKind::Anime)
                    <x-section-nav class="flex flex-nowrap justify-between mb-5 pt-4 xl:safe-area-inset-scroll">
                        <x-slot:title>
                            {{ __('Cast') }}
                        </x-slot:title>

                        <x-slot:action>
                            @hasrole('superAdmin')
                                <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                            @endhasrole
                            <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-nowrap gap-4 justify-start pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                        @foreach ($this->cast as $castEntry)
                            <x-lockups.cast-lockup :cast="$castEntry" />
                        @endforeach
                    </div>
                    @break

                @case (\App\Enums\UserLibraryKind::Manga)
                    <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                        <x-slot:title>
                            {{ __('Cast') }}
                        </x-slot:title>

                        <x-slot:action>
                            @hasrole('superAdmin')
                                <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                            @endhasrole
                            <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <x-rows.character-lockup :manga-casts="$this->cast" />
                    @break

                @default
                    <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                        <x-slot:title>
                            {{ __('Cast') }}
                        </x-slot:title>

                        <x-slot:action>
                            @hasrole('superAdmin')
                                <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                            @endhasrole
                            <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-section-nav>

                    <div class="flex flex-nowrap gap-4 justify-start pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                        @foreach ($this->cast as $castEntry)
                            <x-lockups.cast-lockup :cast="$castEntry" />
                        @endforeach
                    </div>
            @endswitch
        </section>
    @endif
</div>
